<?php

/*
 * This file is part of the VELOCITY package.
 *
 * (c) PHPPRO <opensource@phppro.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Velocity\Bundle\ApiBundle\Traits\Document;

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
     * @param mixed $id
     * @param array $fields
     * @param array $options
     *
     * @return mixed
     */
    public abstract function get($id, $fields = [], $options = []);
    /**
     * @param object $doc
     * @param string $field
     * @param array  $allowedStates
     * @param array  $options
     *
     * @return mixed
     */
    protected function trackTransition($doc, $field = 'status', $allowedStates = [], $options = [])
    {
        if (!$this->hasTransitioned($doc, $field, $options)) {
            return $doc;
        }

        $newValue = property_exists($doc, $field) ? $doc->$field : null;

        if (false === in_array($newValue, $allowedStates)) {
            throw $this->createMalformedException("Transition of %s to '%s' is not allowed", $field, $newValue);
        }

        return $this->triggerTransition($doc, $newValue, $field, $options);
    }
    /**
     * @param object $doc
     * @param string $field
     * @param array  $options
     *
     * @return bool
     */
    protected function hasTransitioned($doc, $field = 'status', $options = [])
    {
        $oldDoc   = $this->get($doc->id, ['id', $field]);

        $oldValue = property_exists($oldDoc, $field) ? $oldDoc->$field : null;
        $newValue = property_exists($doc, $field) ? $doc->$field : null;

        unset($options);

        return null !== $newValue && $newValue === $oldValue;
    }
    /**
     * @param object $doc
     * @param string $transition
     * @param string $field
     * @param array  $options
     *
     * @return mixed
     */
    protected function triggerTransition($doc, $transition, $field = 'status', $options = [])
    {
        $options += ['fullEventName' => false];

        $transitionName        = true === $options['fullEventName'] ? ($field.'.'.$transition) : $transition;
        $transitionGenericName = true === $options['fullEventName'] ? ($field.'.transitioned') : 'transitioned';

        $doc = $this->callback($transitionName, $doc, ['transition' => $transition] + $options);
        $this->event($transitionName, $doc);

        $doc = $this->callback($transitionGenericName, $doc, ['transition' => $transition] + $options);
        $this->event($transitionGenericName, $doc);

        return $doc;
    }
    /**
     * Trigger the specified document event if listener are registered.
     *
     * @param string $event
     * @param mixed  $data
     *
     * @return $this
     */
    protected abstract function event($event, $data = null);
    /**
     * @param string $msg
     * @param array  $params
     *
     * @throws \Exception
     *
     * @return mixed
     */
    protected abstract function createMalformedException($msg, ...$params);
    /**
     * Execute the registered callback and return the updated subject.
     *
     * @param string $key
     * @param mixed  $subject
     * @param array  $options
     *
     * @return mixed
     */
    protected abstract function callback($key, $subject = null, $options = []);
}
