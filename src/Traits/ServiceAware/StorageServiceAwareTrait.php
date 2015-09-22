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

use Velocity\Bundle\ApiBundle\Service\StorageService;

/**
 * StorageServiceAware trait.
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
trait StorageServiceAwareTrait
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
     * @return StorageService
     */
    public function getStorageService()
    {
        return $this->getService('storage');
    }
    /**
     * @param StorageService $service
     *
     * @return $this
     */
    public function setStorageService(StorageService $service)
    {
        return $this->setService('storage', $service);
    }
}
