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
 * Purge service trait.
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
trait PurgeServiceTrait
{
    /**
     * Purge all the documents matching the specified criteria.
     *
     * @param string $parentId
     * @param array  $criteria
     * @param array  $options
     *
     * @return mixed
     */
    public function purge($parentId, $criteria = [], $options = [])
    {
        if ([] !== $criteria) {
            throw $this->createException(500, 'Purging sub documents with criteria not supported');
        }


        $this->callback($parentId, 'pre_purge', [], $options);

        $this->savePurge($parentId, [], $options);

        $this->callback($parentId, 'purged', $criteria, $options);
        $this->event($parentId, 'purged');

        unset($options);
        unset($criteria);

        return $this;
    }
    /**
     * @param mixed $parentId
     * @param array $criteria
     * @param array $options
     *
     * @return mixed
     */
    protected abstract function savePurge($parentId, array $criteria = [], array $options = []);
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
