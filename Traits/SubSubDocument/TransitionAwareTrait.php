<?php

/*
 * This file is part of the VELOCITY package.
 *
 * (c) PHPPRO <opensource@phppro.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Velocity\Bundle\ApiBundle\Traits\SubSubDocument;

/**
 * Transition aware trait.
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
trait TransitionAwareTrait
{
    /**
     * Return the specified document.
     *
     * @param mixed $pParentId
     * @param mixed $parentId
     * @param mixed $id
     * @param array $fields
     * @param array $options
     *
     * @return mixed
     */
    public abstract function get($pParentId, $parentId, $id, $fields = [], $options = []);
    /**
     * @param mixed  $pParentId
     * @param mixed  $parentId
     * @param mixed  $doc
     * @param string $field
     * @param array  $allowedStates
     * @param array  $options
     *
     * @return mixed
     */
    protected function trackTransition($pParentId, $parentId, $doc, $field = 'status', $allowedStates = [], $options = [])
    {
        if (!$this->hasTransitioned($pParentId, $parentId, $doc, $field, $options)) {
            return $doc;
        }

        $newValue = property_exists($doc, $field) ? $doc->$field : null;

        if (false === in_array($newValue, $allowedStates)) {
            throw $this->createException(412, "Transition of %s to '%s' is not allowed", $field, $newValue);
        }

        return $this->triggerTransition($pParentId, $parentId, $doc, $newValue, $field, $options);
    }
    /**
     * @param mixed  $pParentId
     * @param mixed  $parentId
     * @param mixed  $doc
     * @param string $field
     * @param array  $options
     *
     * @return bool
     */
    protected function hasTransitioned($pParentId, $parentId, $doc, $field = 'status', $options = [])
    {
        $oldDoc   = $this->get($pParentId, $parentId, $doc->id, ['id', $field]);

        $oldValue = property_exists($oldDoc, $field) ? $oldDoc->$field : null;
        $newValue = property_exists($doc, $field) ? $doc->$field : null;

        unset($options);

        return null !== $newValue && $newValue === $oldValue;
    }
    /**
     * @param mixed  $pParentId
     * @param mixed  $parentId
     * @param mixed  $doc
     * @param string $transition
     * @param string $field
     * @param array  $options
     *
     * @return $this
     */
    protected function triggerTransition($pParentId, $parentId, $doc, $transition, $field = 'status', $options = [])
    {
        $options += ['fullEventName' => false];

        $this->event($pParentId, $parentId, true === $options['fullEventName'] ? ($field.'.'.$transition) : $transition, $doc);

        return $this;
    }
    /**
     * Trigger the specified document event if listener are registered.
     *
     * @param string $pParentId
     * @param string $parentId
     * @param string $event
     * @param mixed  $data
     *
     * @return $this
     */
    protected abstract function event($pParentId, $parentId, $event, $data = null);
    /**
     * @param int    $code
     * @param string $msg
     * @param array  $params
     *
     * @throws \Exception
     *
     * @return mixed
     */
    protected abstract function createException($code, $msg, ...$params);
}
