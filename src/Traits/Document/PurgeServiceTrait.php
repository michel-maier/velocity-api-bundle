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
 * Purge service trait.
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
trait PurgeServiceTrait
{
    /**
     * Purge all the documents matching the specified criteria.
     *
     * @param array $criteria
     * @param array $options
     *
     * @return mixed
     */
    public function purge($criteria = [], $options = [])
    {
        $this->callback('pre_purge', $criteria, $options);

        $this->savePurge($criteria, $options);

        $this->callback('purged', $criteria, $options);
        $this->event('purged');

        return $this;
    }
    /**
     * @param array $criteria
     * @param array $options
     *
     * @return mixed
     */
    protected abstract function savePurge(array $criteria = [], array $options = []);
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
     * Trigger the specified document event if listener are registered.
     *
     * @param string $event
     * @param mixed  $data
     *
     * @return $this
     */
    protected abstract function event($event, $data = null);
}
