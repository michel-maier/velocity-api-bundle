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
     * @param string $parentId
     * @param array  $data
     * @param array  $options
     *
     * @return mixed
     */
    public function replaceAll($parentId, $data, $options = [])
    {
        $this->callback($parentId, 'pre_empty', []);

        $this->saveDeleteFound($parentId, [], $options);

        $this->callback($parentId, 'emptied', []);
        $this->event($parentId, 'emptied');

        return $this->createBulk($parentId, $data, $options);
    }
    /**
     * Replace all the specified documents.
     *
     * @param mixed $parentId
     * @param array $bulkData
     * @param array $options
     *
     * @return mixed
     */
    public function replaceBulk($parentId, $bulkData, $options = [])
    {
        $this->checkBulkData($bulkData, $options);

        $ids = [];

        foreach ($bulkData as $k => $v) {
            if (!isset($v['id'])) {
                throw $this->createRequiredException('Missing id for item #%s', $k);
            }
            $ids[] = $v['id'];
        }

        $this->deleteBulk($parentId, $ids, $options);

        unset($ids);

        return $this->createBulk($parentId, $bulkData, $options);
    }
    /**
     * @param mixed $parentId
     * @param array $bulkData
     * @param array $options
     *
     * @return $this
     */
    public abstract function updateBulk($parentId, $bulkData, $options = []);
    /**
     * Create a list of documents.
     *
     * @param mixed $parentId
     * @param mixed $bulkData
     * @param array $options
     *
     * @return mixed
     */
    public abstract function createBulk($parentId, $bulkData, $options = []);
    /**
     * Delete the specified documents.
     *
     * @param mixed $parentId
     * @param array $ids
     * @param array $options
     *
     * @return mixed
     */
    public abstract function deleteBulk($parentId, $ids, $options = []);
    /**
     * @param mixed $parentId
     * @param array $criteria
     * @param array $options
     *
     * @return mixed
     */
    protected abstract function saveDeleteFound($parentId, array $criteria, array $options);
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
     * @param mixed $bulkData
     * @param array $options
     *
     * @return $this
     *
     * @throws \Exception
     */
    protected abstract function checkBulkData($bulkData, $options = []);
    /**
     * @param string $msg
     * @param array  $params
     *
     * @throws \Exception
     *
     * @return mixed
     */
    protected abstract function createRequiredException($msg, ...$params);
}
