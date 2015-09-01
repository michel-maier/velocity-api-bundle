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
trait ReplaceServiceTrait
{
    /**
     * Replace all the specified documents.
     *
     * @param string $pParentId
     * @param string $parentId
     * @param array  $data
     * @param array  $options
     *
     * @return mixed
     */
    public function replaceAll($pParentId, $parentId, $data, $options = [])
    {
        $this->callback($pParentId, $parentId, 'pre_empty', []);

        $this->saveDeleteFound($pParentId, $parentId, [], $options);

        $this->callback($pParentId, $parentId, 'emptied', []);
        $this->event($pParentId, $parentId, 'emptied');

        return $this->createBulk($pParentId, $parentId, $data, $options);
    }
    /**
     * Replace all the specified documents.
     *
     * @param mixed $pParentId
     * @param mixed $parentId
     * @param array $bulkData
     * @param array $options
     *
     * @return mixed
     */
    public function replaceBulk($pParentId, $parentId, $bulkData, $options = [])
    {
        $this->checkBulkData($bulkData, $options);

        $ids = [];

        foreach ($bulkData as $k => $v) {
            if (!isset($v['id'])) {
                throw $this->createException(412, 'Missing id for item #%s', $k);
            }
            $ids[] = $v['id'];
        }

        $this->deleteBulk($pParentId, $parentId, $ids, $options);

        unset($ids);

        return $this->createBulk($pParentId, $parentId, $bulkData, $options);
    }
    /**
     * @param mixed $pParentId
     * @param mixed $parentId
     * @param array $bulkData
     * @param array $options
     *
     * @return $this
     */
    public abstract function updateBulk($pParentId, $parentId, $bulkData, $options = []);
    /**
     * Create a list of documents.
     *
     * @param mixed $pParentId
     * @param mixed $parentId
     * @param mixed $bulkData
     * @param array $options
     *
     * @return mixed
     */
    public abstract function createBulk($pParentId, $parentId, $bulkData, $options = []);
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
    public abstract function deleteBulk($pParentId, $parentId, $ids, $options = []);
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
     * @param mixed $bulkData
     * @param array $options
     *
     * @return $this
     *
     * @throws \Exception
     */
    protected abstract function checkBulkData($bulkData, $options = []);
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
