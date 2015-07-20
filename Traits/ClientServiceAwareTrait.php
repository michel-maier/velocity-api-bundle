<?php

/*
 * This file is part of the VELOCITY package.
 *
 * (c) PHPPRO <opensource@phppro.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Velocity\Bundle\ApiBundle\Traits;

use Velocity\Bundle\ApiBundle\Service\ClientServiceInterface;

/**
 * ClientServiceAware trait.
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
trait ClientServiceAwareTrait
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
     * @param ClientServiceInterface $service
     *
     * @return $this
     */
    public function setClientService(ClientServiceInterface $service)
    {
        return $this->setService('client', $service);
    }
    /**
     * @return ClientServiceInterface
     */
    public function getClientService()
    {
        return $this->getService('client');
    }
}