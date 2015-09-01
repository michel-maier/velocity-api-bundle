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
     * @param mixed $pParentId
     * @param mixed $parentId
     * @param mixed $id
     * @param array $options
     *
     * @return mixed
     *
     * @throws Exception
     */
    public function delete($pParentId, $parentId, $id, $options = [])
    {
        list($old) = $this->prepareDelete($pParentId, $parentId, $id, $options);

        $this->saveDelete($pParentId, $parentId, $id, $options);

        return $this->completeDelete($pParentId, $parentId, $id, $old, $options);
    }
    /**
     * Delete the specified documents.
     *
     * @param mixed $pParentId
     * @param mixed $parentId
     * @param array $ids
     * @param array $options
     *
     * @return mixed
     */
    public function deleteBulk($pParentId, $parentId, $ids, $options = [])
    {
        $this->checkBulkData($ids, $options);

        $olds     = [];
        $deleteds = [];

        foreach ($ids as $id) {
            list($olds[$id])  = $this->prepareDelete($pParentId, $parentId, $id, $options);
            $this->pushDeleteInBulk($parentId, $deleteds, $id);
        }

        if (count($deleteds)) {
            $this->saveDeleteBulk($pParentId, $parentId, $deleteds, $options);
        }

        foreach ($ids as $id) {
            $deleteds[$id] = $this->completeDelete($pParentId, $parentId, $id, $olds[$id], $options);
            unset($olds[$id], $ids[$id]);
        }

        unset($ods, $ids);

        return $deleteds;
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
     * @param mixed  $pParentId
     * @param mixed  $parentId
     * @param string $id
     * @param array  $options
     *
     * @return mixed
     */
    protected abstract function saveDelete($pParentId, $parentId, $id, array $options);
    /**
     * @param mixed $pParentId
     * @param mixed $parentId
     * @param array $ids
     * @param array $options
     *
     * @return array
     */
    protected abstract function saveDeleteBulk($pParentId, $parentId, $ids, array $options);
    /**
     * @param mixed $parentId
     * @param array $arrays
     * @param mixed $id
     *
     * @return mixed
     */
    protected abstract function pushDeleteInBulk($parentId, &$arrays, $id);
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
     * @param mixed $pParentId
     * @param mixed $parentId
     * @param mixed $id
     * @param array $options
     *
     * @return array
     */
    protected function prepareDelete($pParentId, $parentId, $id, $options = [])
    {
        $old = $this->get($pParentId, $parentId, $id, [], $options);

        $this->callback($pParentId, $parentId, 'delete.pre_save', $old, $options);

        $this->checkBusinessRules($pParentId, $parentId, 'delete', $old, $options);

        $this->callback($pParentId, $parentId, 'delete.pre_save_checked', $old, $options);

        return [$old];
    }
    /**
     * @param mixed $pParentId
     * @param mixed $parentId
     * @param mixed $id
     * @param mixed $old
     * @param array $options
     *
     * @return mixed
     */
    protected function completeDelete($pParentId, $parentId, $id, $old, $options = [])
    {
        $this->callback($pParentId, $parentId, 'delete.saved', $old, $options);
        $this->callback($pParentId, $parentId, 'deleted', $old, $options);

        $this->event($pParentId, $parentId, 'deleted', $id);
        $this->event($pParentId, $parentId, 'deleted_old', $old);

        return $old;
    }
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
    protected abstract function checkBusinessRules($pParentId, $parentId, $operation, $model, array $options = []);
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
}
