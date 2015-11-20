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
 * Update service trait.
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
trait UpdateServiceTrait
{
    /**
     * @param string $id
     * @param array  $data
     * @param array  $options
     *
     * @return $this
     */
    public function update($id, $data, $options = [])
    {
        list($doc, $array, $old, $transitions) = $this->prepareUpdate($id, $data, $options);

        unset($data);

        $this->saveUpdate($id, $array, $options);

        return $this->completeUpdate($id, $doc, $array, $old, $transitions, $options);
    }
    /**
     * @param mixed $fieldName
     * @param mixed $fieldValue
     * @param array $data
     * @param array $options
     *
     * @return $this
     */
    public function updateBy($fieldName, $fieldValue, $data, $options = [])
    {
        return $this->update($this->getBy($fieldName, $fieldValue, ['id'], $options)->id, $data, $options);
    }
    /**
     * @param array $bulkData
     * @param array $options
     *
     * @return $this
     */
    public function updateBulk($bulkData, $options = [])
    {
        $this->checkBulkData($bulkData, $options);

        $docs   = [];
        $arrays = [];
        $olds   = [];
        $transitions = [];

        foreach ($bulkData as $i => $data) {
            $id = $data['id'];
            unset($data['id']);
            list($docs[$i], $arrays[$i], $olds[$i], $transitions[$i]) = $this->prepareUpdate($id, $data, $options);
            unset($bulkData[$i]);
        }

        unset($bulkData);

        foreach ($this->saveUpdateBulk($arrays, $options) as $i => $array) {
            unset($arrays[$i]);
            $this->completeUpdate($docs[$i], $array, $olds[$i], $transitions[$i], $options);
            unset($olds[$i], $transitions[$i]);
        }

        unset($olds);
        unset($arrays);

        return $docs;
    }
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
     * Return the specified document by the specified field.
     *
     * @param string $fieldName
     * @param mixed  $fieldValue
     * @param array  $fields
     * @param array  $options
     *
     * @return mixed
     */
    public abstract function getBy($fieldName, $fieldValue, $fields = [], $options = []);
    /**
     * @param string $id
     * @param array  $array
     * @param array  $options
     *
     * @return mixed
     */
    protected abstract function saveUpdate($id, array $array, array $options);
    /**
     * @param array  $arrays
     * @param array  $options
     *
     * @return array
     */
    protected abstract function saveUpdateBulk(array $arrays, array $options);
    /**
     * @param array $criteria
     * @param array $options
     *
     * @return mixed
     */
    protected abstract function saveDeleteFound(array $criteria, array $options);
    /**
     * @param string $id
     * @param string $property
     * @param mixed  $value
     * @param array  $options
     *
     * @return mixed
     */
    protected abstract function saveIncrementProperty($id, $property, $value, array $options);
    /**
     * @param string $id
     * @param string $property
     * @param mixed  $value
     * @param array  $options
     */
    protected abstract function saveDecrementProperty($id, $property, $value, array $options);
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
     * Execute the registered callback and return the updated subject.
     *
     * @param string $key
     * @param mixed  $subject
     * @param array  $options
     *
     * @return mixed
     */
    protected abstract function callback($key, $subject = null, $options = []);
    /**
     * @param string $operation
     * @param mixed  $model
     * @param array  $options
     *
     * @return $this
     */
    protected abstract function applyBusinessRules($operation, $model, array $options = []);
    /**
     * @param mixed $model
     * @param array $options
     *
     * @return bool
     */
    protected abstract function hasActiveWorkflows($model, array $options = []);
    /**
     * @param mixed $model
     * @param array $options
     *
     * @return array
     */
    protected abstract function getActiveWorkflowsRequiredFields($model, array $options = []);
    /**
     * @param mixed $model
     * @param mixed $previousModel
     * @param array $options
     *
     * @return array
     */
    protected abstract function applyActiveWorkflows($model, $previousModel, array $options = []);
    /**
     * Test if specified document event has registered event listeners.
     *
     * @param string $event
     *
     * @return bool
     */
    protected abstract function observed($event);
    /**
     * @param mixed $bulkData
     * @param array $options
     *
     * @return $this
     *
     * @throws \Exception
     */
    protected abstract function checkBulkData($bulkData, $options = []);
    /**
     * @param string $mode
     * @param array  $data
     * @param array  $options
     *
     * @return mixed
     */
    protected abstract function validateData(array $data = [], $mode = 'create', array $options = []);
    /**
     * @param mixed $model
     * @param array $options
     *
     * @return mixed
     */
    protected abstract function refreshModel($model, array $options = []);
    /**
     * Convert provided model (object) to an array.
     *
     * @param mixed $model
     * @param array $options
     *
     * @return array
     */
    protected abstract function convertToArray($model, array $options = []);
    /**
     * @param mixed $id
     * @param array $data
     * @param array $options
     *
     * @return array
     */
    protected function prepareUpdate($id, $data = [], $options = [])
    {
        $data = $this->callback('update.pre_validate', $data, $options);
        $doc  = $this->validateData($data, 'update', ['clearMissing' => false] + $options);

        $old = null;
        $hasWorkflows = false;
        $activeWorkflowsRequiredFields = [];

        if ($this->hasActiveWorkflows($doc, $options)) {
            $hasWorkflows = true;
            $activeWorkflowsRequiredFields = $this->getActiveWorkflowsRequiredFields($doc, $options);
        }

        if (true === $hasWorkflows || $this->observed('updated_old') || $this->observed('updated_full_old')) {
            $old = $this->get($id, array_unique(array_merge($activeWorkflowsRequiredFields, array_keys($data))), $options);
        }

        unset($data, $activeWorkflowsRequiredFields);

        $doc = $this->callback('update.validated', $doc, $options);
        $doc = $this->refreshModel($doc, ['operation' => 'update', 'populateNulls' => false, 'id' => $id] + $options);
        $doc = $this->callback('pre_save', $doc, $options);
        $doc = $this->callback('update.pre_save', $doc, $options);

        $this->applyBusinessRules('update', $doc, $options);

        $transitions = [];

        if ($hasWorkflows) {
            $transitions = $this->applyActiveWorkflows($doc, $old, $options);
        }

        $doc   = $this->callback('update.pre_save_checked', $doc, $options);
        $array = $this->convertToArray($doc, $options);
        $array = $this->callback('update.pre_save_array', $array, $options);

        return [$doc, $array, $old, $transitions];
    }
    /**
     * @param mixed $id
     * @param mixed $doc
     * @param array $array
     * @param mixed $old
     * @param array $transitions
     * @param array $options
     *
     * @return mixed
     */
    protected function completeUpdate($id, $doc, $array, $old, $transitions = [], $options = [])
    {
        $this->callback('update.saved_array', $array, $options);

        if (property_exists($doc, 'id') && null === $doc->id) {
            $doc->id = (string) $id;
        }

        unset($array);

        $doc = $this->callback('update.saved', $doc, $options);
        $doc = $this->callback('saved', $doc, $options);
        $doc = $this->callback('updated', $doc, $options);

        $this->applyBusinessRules('complete_update', $doc, $options);

        foreach ($transitions as $transition) {
            $this->applyBusinessRules('complete_update.'.$transition, $doc, $options);
        }

        $this->event('updated', $doc);

        foreach ($transitions as $transition) {
            $this->event('updated.'.$transition, $doc);
        }

        if ($this->observed('updated_old')) {
            $this->event('updated_old', ['new' => $doc, 'old' => $old]);
            foreach ($transitions as $transition) {
                $this->event('updated_old.'.$transition, ['new' => $doc, 'old' => $old]);
            }
        }

        if ($this->observed('updated_full') || $this->observed('updated_full_old')) {
            $full = $this->get($id, [], $options);
            if ($this->observed('updated_full')) {
                $this->event('updated_full', $doc);
                foreach ($transitions as $transition) {
                    $this->event('updated_full.'.$transition, $doc);
                }
            }
            if ($this->observed('updated_full_old')) {
                $this->event('updated_full_old', ['new' => $full, 'old' => $old]);
                foreach ($transitions as $transition) {
                    $this->event('updated_full_old.'.$transition, ['new' => $full, 'old' => $old]);
                }
            }
            unset($full);
        }

        unset($old);

        return $doc;
    }
    /**
     * Increment the specified property of the specified document.
     *
     * @param mixed        $id
     * @param string|array $property
     * @param int          $value
     * @param array        $options
     *
     * @return $this
     */
    public function increment($id, $property, $value = 1, $options = [])
    {
        $this->callback('pre_increment', ['id' => $id, 'value' => $value, 'property' => $property]);
        $this->callback('pre_increment.'.$property, ['id' => $id, 'value' => $value]);

        $this->saveIncrementProperty($id, $property, $value, $options);

        $this->callback('incremented', ['id' => $id, 'value' => $value, 'property' => $property]);
        $this->callback('incremented.'.$property, ['id' => $id, 'value' => $value]);
        $this->event('incremented', ['id' => $id, 'value' => $value, 'property' => $property]);
        $this->event('incremented.'.$property, ['id' => $id, 'value' => $value]);

        return $this;
    }
    /**
     * Decrement the specified property of the specified document.
     *
     * @param mixed        $id
     * @param string|array $property
     * @param int          $value
     * @param array        $options
     *
     * @return $this
     */
    public function decrement($id, $property, $value = 1, $options = [])
    {
        $this->callback('pre_decrement', ['id' => $id, 'value' => $value, 'property' => $property]);
        $this->callback('pre_decrement.'.$property, ['id' => $id, 'value' => $value]);

        $this->saveDecrementProperty($id, $property, $value, $options);

        $this->callback('decremented', ['id' => $id, 'value' => $value, 'property' => $property]);
        $this->callback('decremented.'.$property, ['id' => $id, 'value' => $value]);
        $this->event('decremented', ['id' => $id, 'value' => $value, 'property' => $property]);
        $this->event('decremented.'.$property, ['id' => $id, 'value' => $value]);

        return $this;
    }
}
