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

use Velocity\Bundle\ApiBundle\Service\ArchiverService;

/**
 * ArchiverServiceAware trait.
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
trait ArchiverServiceAwareTrait
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
     * @return ArchiverService
     */
    public function getArchiverService()
    {
        return $this->getService('archiver');
    }
    /**
     * @param ArchiverService $service
     *
     * @return $this
     */
    public function setArchiverService(ArchiverService $service)
    {
        return $this->setService('archiver', $service);
    }
}
