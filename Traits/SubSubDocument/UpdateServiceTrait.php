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
 * Update service trait.
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
trait UpdateServiceTrait
{
    /**
     * @param string $pParentId
     * @param string $parentId
     * @param string $id
     * @param array  $data
     * @param array  $options
     *
     * @return $this
     */
    public function update($pParentId, $parentId, $id, $data, $options = [])
    {
        list($doc, $array, $old) = $this->prepareUpdate($pParentId, $parentId, $id, $data, $options);

        unset($data);

        $this->saveUpdate($pParentId, $parentId, $id, $array, $options);

        return $this->completeUpdate($pParentId, $parentId, $id, $doc, $array, $old, $options);
    }
    /**
     * @param mixed $pParentId
     * @param mixed $parentId
     * @param mixed $fieldName
     * @param mixed $fieldValue
     * @param array $data
     * @param array $options
     *
     * @return $this
     */
    public function updateBy($pParentId, $parentId, $fieldName, $fieldValue, $data, $options = [])
    {
        $docs = $this->find($pParentId, $parentId, [$fieldName => $fieldValue], ['id'], 1, 0, $options);

        if (!count($docs)) {
            throw $this->createNotFoundException("Unknown %s with %s '%s' (%s)", join(' ', $this->getTypes()), $fieldName, $fieldValue, $parentId);
        }

        return $this->update($pParentId, $parentId, array_shift($docs)->id, $data, $options);
    }
    /**
     * @param string $pParentId
     * @param string $parentId
     * @param array  $bulkData
     * @param array  $options
     *
     * @return $this
     */
    public function updateBulk($pParentId, $parentId, $bulkData, $options = [])
    {
        $this->checkBulkData($bulkData, $options);

        $docs    = [];
        $changes = [];
        $olds    = [];
        $arrays  = [];

        foreach ($bulkData as $i => $data) {
            $id = $data['id'];
            unset($data['id']);
            list($docs[$i], $arrays[$i], $olds[$i]) = $this->prepareUpdate($pParentId, $parentId, $id, $data, $options);
            unset($bulkData[$i]);
            $this->pushUpdateInBulk($parentId, $changes, $arrays[$i], $id);
        }

        $this->saveUpdateBulk($pParentId, $parentId, $changes, $options);

        foreach ($arrays as $i => $array) {
            $this->completeUpdate($pParentId, $parentId, $i, $docs[$i], $array, $olds[$i], $options);
            unset($arrays[$array], $olds[$i]);
        }

        unset($olds);
        unset($arrays);

        return $docs;
    }
    /**
     * Increment the specified property of the specified document.
     *
     * @param string       $pParentId
     * @param string       $parentId
     * @param mixed        $id
     * @param string|array $property
     * @param int          $value
     * @param array        $options
     *
     * @return $this
     */
    public function increment($pParentId, $parentId, $id, $property, $value = 1, $options = [])
    {
        $this->callback($pParentId, $parentId, 'pre_increment', ['id' => $id, 'value' => $value, 'property' => $property]);
        $this->callback($pParentId, $parentId, 'pre_increment.'.$property, ['id' => $id, 'value' => $value]);

        $this->saveIncrementProperty($pParentId, $parentId, $id, $property, $value, $options);

        $this->callback($pParentId, $parentId, 'incremented', ['id' => $id, 'value' => $value, 'property' => $property]);
        $this->callback($pParentId, $parentId, 'incremented.'.$property, ['id' => $id, 'value' => $value]);
        $this->event($pParentId, $parentId, 'incremented', ['id' => $id, 'value' => $value, 'property' => $property]);
        $this->event($pParentId, $parentId, 'incremented.'.$property, ['id' => $id, 'value' => $value]);

        return $this;
    }
    /**
     * Decrement the specified property of the specified document.
     *
     * @param string       $pParentId
     * @param string       $parentId
     * @param mixed        $id
     * @param string|array $property
     * @param int          $value
     * @param array        $options
     *
     * @return $this
     */
    public function decrement($pParentId, $parentId, $id, $property, $value = 1, $options = [])
    {
        $this->callback($pParentId, $parentId, 'pre_decrement', ['id' => $id, 'value' => $value, 'property' => $property]);
        $this->callback($pParentId, $parentId, 'pre_decrement.'.$property, ['id' => $id, 'value' => $value]);

        $this->saveDecrementProperty($pParentId, $parentId, $id, $property, $value, $options);

        $this->callback($pParentId, $parentId, 'decremented', ['id' => $id, 'value' => $value, 'property' => $property]);
        $this->callback($pParentId, $parentId, 'decremented.'.$property, ['id' => $id, 'value' => $value]);
        $this->event($pParentId, $parentId, 'decremented', ['id' => $id, 'value' => $value, 'property' => $property]);
        $this->event($pParentId, $parentId, 'decremented.'.$property, ['id' => $id, 'value' => $value]);

        return $this;
    }
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
     * Retrieve the documents matching the specified criteria.
     *
     * @param string   $pParentId
     * @param string   $parentId
     * @param array    $criteria
     * @param array    $fields
     * @param null|int $limit
     * @param int      $offset
     * @param array    $sorts
     * @param array    $options
     *
     * @return mixed
     */
    public abstract function find($pParentId, $parentId, $criteria = [], $fields = [], $limit = null, $offset = 0, $sorts = [], $options = []);
    /**
     * @return array
     */
    public abstract function getTypes();
    /**
     * @param mixed  $pParentId
     * @param mixed  $parentId
     * @param string $id
     * @param array  $array
     * @param array  $options
     *
     * @return mixed
     */
    protected abstract function saveUpdate($pParentId, $parentId, $id, array $array, array $options);
    /**
     * @param mixed $pParentId
     * @param mixed $parentId
     * @param array $arrays
     * @param array $options
     *
     * @return array
     */
    protected abstract function saveUpdateBulk($pParentId, $parentId, array $arrays, array $options);
    /**
     * @param mixed $pParentId
     * @param mixed $parentId
     * @param array $criteria
     * @param array $options
     *
     * @return mixed
     */
    protected abstract function saveDeleteFound($pParentId, $parentId, array $criteria, array $options);
    /**
     * @param mixed  $pParentId
     * @param mixed  $parentId
     * @param string $id
     * @param string $property
     * @param mixed  $value
     * @param array  $options
     *
     * @return mixed
     */
    protected abstract function saveIncrementProperty($pParentId, $parentId, $id, $property, $value, array $options);
    /**
     * @param mixed  $pParentId
     * @param mixed  $parentId
     * @param string $id
     * @param string $property
     * @param mixed  $value
     * @param array  $options
     */
    protected abstract function saveDecrementProperty($pParentId, $parentId, $id, $property, $value, array $options);
    /**
     * @param mixed $parentId
     * @param array $arrays
     * @param array $array
     * @param mixed $id
     *
     * @return mixed
     */
    protected abstract function pushUpdateInBulk($parentId, &$arrays, $array, $id);
    /**
     * Trigger the specified document event if listener are registered.
     *
     * @param mixed  $pParentId
     * @param mixed  $parentId
     * @param string $event
     * @param mixed  $data
     *
     * @return $this
     */
    protected abstract function event($pParentId, $parentId, $event, $data = null);
    /**
     * Execute the registered callback and return the updated subject.
     *
     * @param mixed  $pParentId
     * @param mixed  $parentId
     * @param string $key
     * @param mixed  $subject
     * @param array  $options
     *
     * @return mixed
     */
    protected abstract function callback($pParentId, $parentId, $key, $subject = null, $options = []);
    /**
     * @param mixed  $pParentId
     * @param mixed  $parentId
     * @param string $operation
     * @param mixed  $model
     * @param array  $options
     *
     * @return $this
     */
    protected abstract function applyBusinessRules($pParentId, $parentId, $operation, $model, array $options = []);
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
     * @param string $pParentId
     * @param string $parentId
     * @param mixed  $id
     * @param array  $data
     * @param array  $options
     *
     * @return array
     */
    protected function prepareUpdate($pParentId, $parentId, $id, $data = [], $options = [])
    {
        $old = null;

        if ($this->observed('updated_old') || $this->observed('updated_full_old')) {
            $old = $this->get($pParentId, $parentId, $id, array_keys($data), $options);
        }

        $data = $this->callback($pParentId, $parentId, 'update.pre_validate', $data, $options);
        $doc  = $this->validateData($data, 'update', ['clearMissing' => false] + $options);

        unset($data);

        $doc = $this->callback($pParentId, $parentId, 'update.validated', $doc, $options);
        $doc = $this->refreshModel($doc, $options);
        $doc = $this->callback($pParentId, $parentId, 'pre_save', $doc, $options);
        $doc = $this->callback($pParentId, $parentId, 'update.pre_save', $doc, $options);

        $this->applyBusinessRules($pParentId, $parentId, 'update', $doc, $options);

        $doc   = $this->callback($pParentId, $parentId, 'update.pre_save_checked', $doc, $options);
        $array = $this->convertToArray($doc, $options);
        $array = $this->callback($pParentId, $parentId, 'update.pre_save_array', $array, $options);

        return [$doc, $array, $old];
    }
    /**
     * @param string $pParentId
     * @param string $parentId
     * @param mixed  $id
     * @param mixed  $doc
     * @param array  $array
     * @param mixed  $old
     * @param array  $options
     *
     * @return mixed
     */
    protected function completeUpdate($pParentId, $parentId, $id, $doc, $array, $old, $options = [])
    {
        $this->callback($pParentId, $parentId, 'update.saved_array', $array, $options);

        unset($array);

        $doc = $this->callback($pParentId, $parentId, 'update.saved', $doc, $options);
        $doc = $this->callback($pParentId, $parentId, 'saved', $doc, $options);
        $doc = $this->callback($pParentId, $parentId, 'updated', $doc, $options);

        $this->applyBusinessRules($pParentId, $parentId, 'complete_update', $doc, $options);

        $this->event($pParentId, $parentId, 'updated', $doc);

        if ($this->observed('updated_old')) {
            $this->event($pParentId, $parentId, 'updated_old', ['new' => $doc, 'old' => $old]);
        }

        if ($this->observed('updated_full') || $this->observed('updated_full_old')) {
            $full = $this->get($pParentId, $parentId, $id, [], $options);
            if ($this->observed('updated_full')) {
                $this->event($pParentId, $parentId, 'updated_full', $doc);
            }
            if ($this->observed('updated_full_old')) {
                $this->event($pParentId, $parentId, 'updated_full_old', ['new' => $full, 'old' => $old]);
            }
            unset($full);
        }

        unset($old);

        return $doc;
    }
    /**
     * @param string $msg
     * @param array  $params
     *
     * @throws \Exception
     *
     * @return mixed
     */
    protected abstract function createNotFoundException($msg, ...$params);
}
