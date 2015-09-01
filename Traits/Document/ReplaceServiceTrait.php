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
trait ReplaceServiceTrait
{
    /**
     * Replace all the specified documents.
     *
     * @param array $data
     * @param array $options
     *
     * @return mixed
     */
    public function replaceAll($data, $options = [])
    {
        $this->callback('pre_empty', []);

        $this->saveDeleteFound([], $options);

        $this->callback('emptied', []);
        $this->event('emptied');

        return $this->createBulk($data, $options);
    }
    /**
     * Replace all the specified documents.
     *
     * @param array $bulkData
     * @param array $options
     *
     * @return mixed
     */
    public function replaceBulk($bulkData, $options = [])
    {
        $this->checkBulkData($bulkData, $options);

        $ids = [];

        foreach ($bulkData as $k => $v) {
            if (!isset($v['id'])) {
                throw $this->createException(412, 'Missing id for item #%s', $k);
            }
            $ids[] = $v['id'];
        }

        $this->deleteBulk($ids, $options);

        unset($ids);

        return $this->createBulk($bulkData, $options);
    }
    /**
     * @param array $bulkData
     * @param array $options
     *
     * @return $this
     */
    public abstract function updateBulk($bulkData, $options = []);
    /**
     * Create a list of documents.
     *
     * @param mixed $bulkData
     * @param array $options
     *
     * @return mixed
     */
    public abstract function createBulk($bulkData, $options = []);
    /**
     * Delete the specified documents.
     *
     * @param array $ids
     * @param array $options
     *
     * @return mixed
     */
    public abstract function deleteBulk($ids, $options = []);
    /**
     * @param array $criteria
     * @param array $options
     *
     * @return mixed
     */
    protected abstract function saveDeleteFound(array $criteria, array $options);
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
