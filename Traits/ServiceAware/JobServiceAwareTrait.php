<?php

/*
 * This file is part of the VELOCITY package.
 *
 * (c) PHPPRO <opensource@phppro.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Velocity\Bundle\ApiBundle\Traits\ServiceAware;

use Velocity\Bundle\ApiBundle\Service\JobService;

/**
 * JobServiceAware trait.
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
trait JobServiceAwareTrait
{
    /**
     * @param string $key
     * @param mixed  $service
     *
     * @return $this
     */
    protected abstract function setService($key, $service);
    /**
     * @param string $key
     *
     * @return mixed
     */
    protected abstract function getService($key);
    /**
     * @return JobService
     */
    public function getJobService()
    {
        return $this->getService('job');
    }
    /**
     * @param JobService $service
     *
     * @return $this
     */
    public function setJobService(JobService $service)
    {
        return $this->setService('job', $service);
    }
}
