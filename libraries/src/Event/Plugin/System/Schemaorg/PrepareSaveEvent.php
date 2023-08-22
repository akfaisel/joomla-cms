<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Event\Plugin\System\Schemaorg;

use Joomla\CMS\Event\AbstractImmutableEvent;
use Joomla\CMS\Table\TableInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Class for SchemaPrepareSaveEvent event
 * Example:
 *  new PrepareFormEvent('onSchemaPrepareForm', ['subject' => $subject, 'context' => $context, 'item' => $table, 'isNew' => $isNew, 'schema' => $schema]);
 *
 * @since  __DEPLOY_VERSION__
 */
class PrepareSaveEvent extends AbstractImmutableEvent
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

        if (!\array_key_exists('item', $arguments)) {
            throw new \BadMethodCallException("Argument 'item' of event {$name} is required but has not been provided");
        }

        if (!\array_key_exists('isNew', $arguments)) {
            throw new \BadMethodCallException("Argument 'isNew' of event {$name} is required but has not been provided");
        }

        if (!\array_key_exists('schema', $arguments)) {
            throw new \BadMethodCallException("Argument 'schema' of event {$name} is required but has not been provided");
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
     * Setter for the item argument.
     *
     * @param   TableInterface  $value  The value to set
     *
     * @return  TableInterface
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function setItem(TableInterface $value): TableInterface
    {
        return $value;
    }

    /**
     * Setter for the isNew argument.
     *
     * @param   boolean  $value  The value to set
     *
     * @return  boolean
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function setIsNew(bool $value): bool
    {
        return $value;
    }

    /**
     * Setter for the schema argument.
     *
     * @param   array  $value  The value to set
     *
     * @return  array
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function setSchema(array $value): array
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

    /**
     * Getter for the item argument.
     *
     * @return  TableInterface
     *
     * @since   __DEPLOY_VERSION__
     */
    public function getItem(): TableInterface
    {
        return $this->arguments['item'];
    }

    /**
     * Getter for the isNew argument.
     *
     * @return  boolean
     *
     * @since   __DEPLOY_VERSION__
     */
    public function getIsNew(): bool
    {
        return $this->arguments['isNew'];
    }

    /**
     * Getter for the schema argument.
     *
     * @return  array
     *
     * @since   __DEPLOY_VERSION__
     */
    public function getSchema(): array
    {
        return $this->arguments['schema'];
    }
}
