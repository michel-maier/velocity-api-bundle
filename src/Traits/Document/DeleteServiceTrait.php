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
 * Delete service trait.
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
trait DeleteServiceTrait
{
    /**
     * Delete the specified document.
     *
     * @param mixed $id
     * @param array $options
     *
     * @return mixed
     *
     * @throws \Exception
     */
    public function delete($id, $options = [])
    {
        list($old) = $this->prepareDelete($id, $options);

        $this->saveDelete($id, $options);

        return $this->completeDelete($id, $old, $options);
    }
    /**
     * Delete the specified documents.
     *
     * @param array $ids
     * @param array $options
     *
     * @return mixed
     */
    public function deleteBulk($ids, $options = [])
    {
        $this->checkBulkData($ids, $options);

        $olds     = [];
        $deleteds = [];

        foreach ($ids as $id) {
            list($olds[$id]) = $this->prepareDelete($id, $options);
            unset($ids[$id]);
        }


        foreach ($this->saveDeleteBulk($ids, $options) as $id) {
            $deleteds[$id] = $this->completeDelete($id, $olds[$id], $options);
            unset($olds[$id]);
        }

        unset($ids);
        unset($olds);

        return $deleteds;
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
     * @param string $id
     * @param array  $options
     *
     * @return mixed
     */
    protected abstract function saveDelete($id, array $options);
    /**
     * @param array $ids
     * @param array $options
     *
     * @return array
     */
    protected abstract function saveDeleteBulk($ids, array $options);
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
     * @param mixed $id
     * @param array $options
     *
     * @return array
     */
    protected function prepareDelete($id, $options = [])
    {
        $old = $this->get($id, [], $options);

        $this->callback('delete.pre_save', $old, $options);

        $this->applyBusinessRules('delete', $old, $options);

        $this->callback('delete.pre_save_checked', $old, $options);

        return [$old];
    }
    /**
     * @param mixed $id
     * @param mixed $old
     * @param array $options
     *
     * @return mixed
     */
    protected function completeDelete($id, $old, $options = [])
    {
        $this->cleanModel($old, ['operation' => 'delete'] + $options);

        $this->callback('delete.saved', $old, $options);
        $this->callback('deleted', $old, $options);

        $this->applyBusinessRules('complete_delete', $old, $options);

        $this->event('deleted', $old);

        unset($old);

        return ['id' => $id, 'status' => 'deleted'];
    }
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
     * Trigger the specified document event if listener are registered.
     *
     * @param string $event
     * @param mixed  $data
     *
     * @return $this
     */
    protected abstract function event($event, $data = null);
    /**
     * @param mixed $model
     * @param array $options
     *
     * @return mixed
     */
    protected abstract function cleanModel($model, array $options = []);
    /**
     * Test if specified document event has registered event listeners.
     *
     * @param string $event
     *
     * @return bool
     */
    protected abstract function observed($event);
}
