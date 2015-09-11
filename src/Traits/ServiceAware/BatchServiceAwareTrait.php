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

use Velocity\Bundle\ApiBundle\Service\BatchService;

/**
 * BatchServiceAware trait.
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
trait BatchServiceAwareTrait
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
     * @return BatchService
     */
    public function getBatchService()
    {
        return $this->getService('batch');
    }
    /**
     * @param BatchService $service
     *
     * @return $this
     */
    public function setBatchService(BatchService $service)
    {
        return $this->setService('batch', $service);
    }
}
