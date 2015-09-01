<?php

/*
 * This file is part of the VELOCITY package.
 *
 * (c) PHPPRO <opensource@phppro.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Velocity\Bundle\ApiBundle\Traits\SubDocument;

use Exception;

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
     * @param string $parentId
     * @param mixed  $id
     * @param array  $options
     *
     * @return mixed
     *
     * @throws Exception
     */
    public function delete($parentId, $id, $options = [])
    {
        list($old) = $this->prepareDelete($parentId, $id, $options);

        $this->saveDelete($parentId, $id, $options);

        return $this->completeDelete($parentId, $id, $old, $options);
    }
    /**
     * Delete the specified documents.
     *
     * @param string $parentId
     * @param array  $ids
     * @param array  $options
     *
     * @return mixed
     */
    public function deleteBulk($parentId, $ids, $options = [])
    {
        $this->checkBulkData($ids, $options);

        $olds     = [];
        $deleteds = [];

        foreach ($ids as $id) {
            list($olds[$id])  = $this->prepareDelete($parentId, $id, $options);
            $this->pushDeleteInBulk($deleteds, $id);
        }

        if (count($deleteds)) {
            $this->saveDeleteBulk($parentId, $deleteds, $options);
        }

        foreach ($ids as $id) {
            $deleteds[$id] = $this->completeDelete($parentId, $id, $olds[$id], $options);
            unset($olds[$id], $ids[$id]);
        }

        unset($ods, $ids);

        return $deleteds;
    }
    /**
     * Return the specified document.
     *
     * @param mixed $parentId
     * @param mixed $id
     * @param array $fields
     * @param array $options
     *
     * @return mixed
     */
    public abstract function get($parentId, $id, $fields = [], $options = []);
    /**
     * @param mixed  $parentId
     * @param string $id
     * @param array  $options
     *
     * @return mixed
     */
    protected abstract function saveDelete($parentId, $id, array $options);
    /**
     * @param mixed $parentId
     * @param array $ids
     * @param array $options
     *
     * @return array
     */
    protected abstract function saveDeleteBulk($parentId, $ids, array $options);
    /**
     * @param array $arrays
     * @param mixed $id
     *
     * @return mixed
     */
    protected abstract function pushDeleteInBulk(&$arrays, $id);
    /**
     * @param mixed $bulkData
     * @param array $options
     *
     * @return $this
     *
     * @throws Exception
     */
    protected abstract function checkBulkData($bulkData, $options = []);
    /**
     * @param string $parentId
     * @param mixed  $id
     * @param array  $options
     *
     * @return array
     */
    protected function prepareDelete($parentId, $id, $options = [])
    {
        $old = $this->get($parentId, $id, [], $options);

        $this->callback($parentId, 'delete.pre_save', $old, $options);

        $this->checkBusinessRules($parentId, 'delete', $old, $options);

        $this->callback($parentId, 'delete.pre_save_checked', $old, $options);

        return [$old];
    }
    /**
     * @param string $parentId
     * @param mixed  $id
     * @param mixed  $old
     * @param array  $options
     *
     * @return mixed
     */
    protected function completeDelete($parentId, $id, $old, $options = [])
    {
        $this->callback($parentId, 'delete.saved', $old, $options);
        $this->callback($parentId, 'deleted', $old, $options);

        $this->event($parentId, 'deleted', $id);
        $this->event($parentId, 'deleted_old', $old);

        return $old;
    }
    /**
     * Execute the registered callback and return the updated subject.
     *
     * @param mixed  $parentId
     * @param string $key
     * @param mixed  $subject
     * @param array  $options
     *
     * @return mixed
     */
    protected abstract function callback($parentId, $key, $subject = null, $options = []);
    /**
     * @param mixed  $parentId
     * @param string $operation
     * @param mixed  $model
     * @param array  $options
     *
     * @return $this
     */
    protected abstract function checkBusinessRules($parentId, $operation, $model, array $options = []);
    /**
     * Trigger the specified document event if listener are registered.
     *
     * @param mixed  $parentId
     * @param string $event
     * @param mixed  $data
     *
     * @return $this
     */
    protected abstract function event($parentId, $event, $data = null);
}
