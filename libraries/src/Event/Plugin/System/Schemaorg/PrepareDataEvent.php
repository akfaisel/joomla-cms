<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Event\Plugin\System\Schemaorg;

use Joomla\CMS\Event\AbstractImmutableEvent;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Class for SchemaPrepareDataEvent event
 * Example:
 *  new PrepareDataEvent('onSchemaPrepareData', ['subject' => $data, 'context' => 'com_example.example']);
 *
 * @since  __DEPLOY_VERSION__
 */
class PrepareDataEvent extends AbstractImmutableEvent
{
    /**
     * Constructor.
     *
     * @param   string  $name       The event name.
     * @param   array   $arguments  The event arguments.
     *
     * @throws  \BadMethodCallException
     *
     * @since   __DEPLOY_VERSION__
     */
    public function __construct($name, array $arguments = [])
    {
        if (!\array_key_exists('subject', $arguments)) {
            throw new \BadMethodCallException("Argument 'subject' of event {$name} is required but has not been provided");
        }

        if (!\array_key_exists('context', $arguments)) {
            throw new \BadMethodCallException("Argument 'context' of event {$name} is required but has not been provided");
        }

        parent::__construct($name, $arguments);
    }

    /**
     * Setter for the subject argument.
     *
     * @param   object  $value  The value to set
     *
     * @return  object
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function setSubject(object $value): object
    {
        return $value;
    }

    /**
     * Setter for the context argument.
     *
     * @param   string  $value  The value to set
     *
     * @return  string
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function setContext(string $value): string
    {
        return $value;
    }

    /**
     * Getter for the data argument.
     *
     * @return  object
     *
     * @since   __DEPLOY_VERSION__
     */
    public function getData(): object
    {
        return $this->arguments['subject'];
    }

    /**
     * Getter for the context argument.
     *
     * @return  string
     *
     * @since   __DEPLOY_VERSION__
     */
    public function getContext(): string
    {
        return $this->arguments['context'];
    }
}
