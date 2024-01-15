<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2008 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Updater;

use Joomla\CMS\Factory;
use Joomla\CMS\Filter\InputFilter;
use Joomla\CMS\Http\HttpFactory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Object\LegacyErrorHandlingTrait;
use Joomla\CMS\Object\LegacyPropertyManagementTrait;
use Joomla\CMS\Version;
use Joomla\Registry\Registry;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Update class. It is used by Updater::update() to install an update. Use Updater::findUpdates() to find updates for
 * an extension.
 *
 * @since  1.7.0
 */
class Update
{
    use LegacyErrorHandlingTrait;
    use LegacyPropertyManagementTrait;

    /**
     * Update manifest `<name>` element
     *
     * @var    string
     * @since  1.7.0
     */
    protected $name;

    /**
     * Update manifest `<description>` element
     *
     * @var    string
     * @since  1.7.0
     */
    protected $description;

    /**
     * Update manifest `<element>` element
     *
     * @var    string
     * @since  1.7.0
     */
    protected $element;

    /**
     * Update manifest `<type>` element
     *
     * @var    string
     * @since  1.7.0
     */
    protected $type;

    /**
     * Update manifest `<version>` element
     *
     * @var    string
     * @since  1.7.0
     */
    protected $version;

    /**
     * Update manifest `<infourl>` element
     *
     * @var    string
     * @since  1.7.0
     */
    protected $infourl;

    /**
     * Update manifest `<client>` element
     *
     * @var    string
     * @since  1.7.0
     */
    protected $client;

    /**
     * Update manifest `<group>` element
     *
     * @var    string
     * @since  1.7.0
     */
    protected $group;

    /**
     * Update manifest `<downloads>` element
     *
     * @var    string
     * @since  1.7.0
     */
    protected $downloads;

    /**
     * Update manifest `<downloadsource>` elements
     *
     * @var    DownloadSource[]
     * @since  3.8.3
     */
    protected $downloadSources = [];

    /**
     * Update manifest `<tags>` element
     *
     * @var    string
     * @since  1.7.0
     */
    protected $tags;

    /**
     * Update manifest `<maintainer>` element
     *
     * @var    string
     * @since  1.7.0
     */
    protected $maintainer;

    /**
     * Update manifest `<maintainerurl>` element
     *
     * @var    string
     * @since  1.7.0
     */
    protected $maintainerurl;

    /**
     * Update manifest `<category>` element
     *
     * @var    string
     * @since  1.7.0
     */
    protected $category;

    /**
     * Update manifest `<relationships>` element
     *
     * @var    string
     * @since  1.7.0
     */
    protected $relationships;

    /**
     * Update manifest `<targetplatform>` element
     *
     * @var    string
     * @since  1.7.0
     */
    protected $targetplatform;

    /**
     * Extra query for download URLs
     *
     * @var    string
     * @since  3.2.0
     */
    protected $extra_query;

    /**
     * Resource handle for the XML Parser
     *
     * @var    resource
     * @since  3.0.0
     */
    protected $xmlParser;

    /**
     * Element call stack
     *
     * @var    array
     * @since  3.0.0
     */
    protected $stack = ['base'];

    /**
     * Unused state array
     *
     * @var    array
     * @since  3.0.0
     */
    protected $stateStore = [];

    /**
     * Object containing the current update data
     *
     * @var    \stdClass
     * @since  3.0.0
     */
    protected $currentUpdate;

    /**
     * Object containing the latest update data which meets the PHP and DB version requirements
     *
     * @var    \stdClass
     * @since  3.0.0
     */
    protected $latest;

    /**
     * Object containing details if the latest update does not meet the PHP and DB version requirements
     *
     * @var    \stdClass
     * @since  4.4.2
     */
    protected $otherUpdateInfo;

    /**
     * The minimum stability required for updates to be taken into account. The possible values are:
     * 0    dev         Development snapshots, nightly builds, pre-release versions and so on
     * 1    alpha       Alpha versions (work in progress, things are likely to be broken)
     * 2    beta        Beta versions (major functionality in place, show-stopper bugs are likely to be present)
     * 3    rc          Release Candidate versions (almost stable, minor bugs might be present)
     * 4    stable      Stable versions (production quality code)
     *
     * @var    integer
     * @since  14.1
     *
     * @see    Updater
     */
    protected $minimum_stability = Updater::STABILITY_STABLE;

    /**
     * Array with compatible versions used by the pre-update check
     *
     * @var    array
     * @since  3.10.2
     */
    protected $compatibleVersions = [];
    public $downloadurl;
    protected $tag;
    protected $stability;
    protected $supported_databases;
    protected $php_minimum;
    public $sha256;
    public $sha384;
    public $sha512;
    protected $section;

    /**
     * Gets the reference to the current direct parent
     *
     * @return  string
     *
     * @since   1.7.0
     */
    protected function _getStackLocation()
    {
        return implode('->', $this->stack);
    }

    /**
     * Get the last position in stack count
     *
     * @return  string
     *
     * @since   1.7.0
     */
    protected function _getLastTag()
    {
        return $this->stack[\count($this->stack) - 1];
    }

    /**
     * XML Start Element callback
     *
     * @param   object  $parser  Parser object
     * @param   string  $name    Name of the tag found
     * @param   array   $attrs   Attributes of the tag
     *
     * @return  void
     *
     * @note    This is public because it is called externally
     * @since   1.7.0
     */
    public function _startElement($parser, $name, $attrs = [])
    {
        $this->stack[] = $name;
        $tag           = $this->_getStackLocation();

        // Reset the data
        if (isset($this->$tag)) {
            $this->$tag->_data = '';
        }

        switch ($name) {
            // This is a new update; create a current update
            case 'UPDATE':
                $this->currentUpdate = new \stdClass();
                break;

            // Handle the array of download sources
            case 'DOWNLOADSOURCE':
                $source = new DownloadSource();

                foreach ($attrs as $key => $data) {
                    $key          = strtolower($key);
                    $source->$key = $data;
                }

                $this->downloadSources[] = $source;

                break;

            // Don't do anything
            case 'UPDATES':
                break;

            // For everything else there's...the default!
            default:
                $name = strtolower($name);

                if (!isset($this->currentUpdate->$name)) {
                    $this->currentUpdate->$name = new \stdClass();
                }

                $this->currentUpdate->$name->_data = '';

                foreach ($attrs as $key => $data) {
                    $key                              = strtolower($key);
                    $this->currentUpdate->$name->$key = $data;
                }
                break;
        }
    }

    /**
     * Callback for closing the element
     *
     * @param   object  $parser  Parser object
     * @param   string  $name    Name of element that was closed
     *
     * @return  void
     *
     * @note    This is public because it is called externally
     * @since   1.7.0
     */
    public function _endElement($parser, $name)
    {
        array_pop($this->stack);

        switch ($name) {
            // Closing update, find the latest version and check
            case 'UPDATE':
                $product = strtolower(InputFilter::getInstance()->clean(Version::PRODUCT, 'cmd'));

                // Check that the product matches and that the version matches (optionally a regexp)
                if (
                    isset($this->currentUpdate->targetplatform->name)
                    && $product == $this->currentUpdate->targetplatform->name
                    && preg_match('/^' . $this->currentUpdate->targetplatform->version . '/', $this->get('jversion.full', JVERSION))
                ) {
                    // Collect information on updates which do not meet PHP and DB version requirements
                    $otherUpdateInfo          = new \stdClass();
                    $otherUpdateInfo->version = $this->currentUpdate->version->_data;

                    $phpMatch = false;

                    // Check if PHP version supported via <php_minimum> tag, assume true if tag isn't present
                    if (!isset($this->currentUpdate->php_minimum) || version_compare(PHP_VERSION, $this->currentUpdate->php_minimum->_data, '>=')) {
                        $phpMatch = true;
                    }

                    if (!$phpMatch) {
                        $otherUpdateInfo->php           = new \stdClass();
                        $otherUpdateInfo->php->required = $this->currentUpdate->php_minimum->_data;
                        $otherUpdateInfo->php->used     = PHP_VERSION;
                    }

                    $dbMatch = false;

                    // Check if DB & version is supported via <supported_databases> tag, assume supported if tag isn't present
                    if (isset($this->currentUpdate->supported_databases)) {
                        $db           = Factory::getDbo();
                        $dbType       = strtolower($db->getServerType());
                        $dbVersion    = $db->getVersion();
                        $supportedDbs = $this->currentUpdate->supported_databases;

                        // MySQL and MariaDB use the same database driver but not the same version numbers
                        if ($dbType === 'mysql') {
                            // Check whether we have a MariaDB version string and extract the proper version from it
                            if (stripos($dbVersion, 'mariadb') !== false) {
                                // MariaDB: Strip off any leading '5.5.5-', if present
                                $dbVersion = preg_replace('/^5\.5\.5-/', '', $dbVersion);
                                $dbType    = 'mariadb';
                            }
                        }

                        // Do we have an entry for the database?
                        if (isset($supportedDbs->$dbType)) {
                            $minimumVersion = $supportedDbs->$dbType;
                            $dbMatch        = version_compare($dbVersion, $minimumVersion, '>=');

                            if (!$dbMatch) {
                                $otherUpdateInfo->db           = new \stdClass();
                                $otherUpdateInfo->db->type     = $dbType;
                                $otherUpdateInfo->db->required = $minimumVersion;
                                $otherUpdateInfo->db->used     = $dbVersion;
                            }
                        }
                    } else {
                        // Set to true if the <supported_databases> tag is not set
                        $dbMatch = true;
                    }

                    // Check minimum stability
                    $stabilityMatch = true;

                    if (isset($this->currentUpdate->stability) && ($this->currentUpdate->stability < $this->minimum_stability)) {
                        $stabilityMatch = false;
                    }

                    if ($phpMatch && $stabilityMatch && $dbMatch) {
                        if (!empty($this->currentUpdate->downloadurl) && !empty($this->currentUpdate->downloadurl->_data)) {
                            $this->compatibleVersions[] = $this->currentUpdate->version->_data;
                        }

                        if (
                            !isset($this->latest)
                            || version_compare($this->currentUpdate->version->_data, $this->latest->version->_data, '>')
                        ) {
                            $this->latest = $this->currentUpdate;
                        }
                    } elseif (
                        !isset($this->otherUpdateInfo)
                        || version_compare($otherUpdateInfo->version, $this->otherUpdateInfo->version, '>')
                    ) {
                        $this->otherUpdateInfo = $otherUpdateInfo;
                    }
                }
                break;
            case 'UPDATES':
                // If the latest item is set then we transfer it to where we want to
                if (isset($this->latest)) {
                    // This is an optional tag and therefore we need to be sure that this is gone and only used when set by the update itself
                    unset($this->downloadSources);

                    foreach (get_object_vars($this->latest) as $key => $val) {
                        $this->$key = $val;
                    }

                    unset($this->latest);
                    unset($this->currentUpdate);
                } elseif (isset($this->currentUpdate)) {
                    // The update might be for an older version of j!
                    unset($this->currentUpdate);
                }
                break;
        }
    }

    /**
     * Character Parser Function
     *
     * @param   object  $parser  Parser object.
     * @param   object  $data    The data.
     *
     * @return  void
     *
     * @note    This is public because its called externally.
     * @since   1.7.0
     */
    public function _characterData($parser, $data)
    {
        $tag = $this->_getLastTag();

        // Throw the data for this item together
        $tag = strtolower($tag);

        if ($tag === 'tag') {
            $this->currentUpdate->stability = $this->stabilityTagToInteger((string) $data);

            return;
        }

        if ($tag === 'downloadsource') {
            // Grab the last source so we can append the URL
            $source      = end($this->downloadSources);
            $source->url = $data;

            return;
        }

        if (isset($this->currentUpdate->$tag)) {
            $this->currentUpdate->$tag->_data .= $data;
        }
    }

    /**
     * Loads an XML file from a URL.
     *
     * @param   string  $url               The URL.
     * @param   int     $minimumStability  The minimum stability required for updating the extension {@see Updater}
     *
     * @return  boolean  True on success
     *
     * @since   1.7.0
     */
    public function loadFromXml($url, $minimumStability = Updater::STABILITY_STABLE)
    {
        $version    = new Version();
        $httpOption = new Registry();
        $httpOption->set('userAgent', $version->getUserAgent('Joomla', true, false));

        try {
            $http     = HttpFactory::getHttp($httpOption);
            $response = $http->get($url);
        } catch (\RuntimeException $e) {
            $response = null;
        }

        if ($response === null || $response->code !== 200) {
            // @todo: Add a 'mark bad' setting here somehow
            Log::add(Text::sprintf('JLIB_UPDATER_ERROR_EXTENSION_OPEN_URL', $url), Log::WARNING, 'jerror');

            return false;
        }

        $this->minimum_stability = $minimumStability;

        $this->xmlParser = xml_parser_create('');
        xml_set_object($this->xmlParser, $this);
        xml_set_element_handler($this->xmlParser, '_startElement', '_endElement');
        xml_set_character_data_handler($this->xmlParser, '_characterData');

        if (!xml_parse($this->xmlParser, $response->body)) {
            Log::add(
                sprintf(
                    'XML error: %s at line %d',
                    xml_error_string(xml_get_error_code($this->xmlParser)),
                    xml_get_current_line_number($this->xmlParser)
                ),
                Log::WARNING,
                'updater'
            );

            return false;
        }

        xml_parser_free($this->xmlParser);

        return true;
    }

    /**
     * Converts a tag to numeric stability representation. If the tag doesn't represent a known stability level (one of
     * dev, alpha, beta, rc, stable) it is ignored.
     *
     * @param   string  $tag  The tag string, e.g. dev, alpha, beta, rc, stable
     *
     * @return  integer
     *
     * @since   3.4
     */
    protected function stabilityTagToInteger($tag)
    {
        $constant = '\\Joomla\\CMS\\Updater\\Updater::STABILITY_' . strtoupper($tag);

        if (\defined($constant)) {
            return \constant($constant);
        }

        return Updater::STABILITY_STABLE;
    }
}
