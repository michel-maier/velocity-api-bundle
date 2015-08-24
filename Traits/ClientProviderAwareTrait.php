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

use Velocity\Bundle\ApiBundle\ClientProviderInterface;

/**
 * ClientProviderAware trait.
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
trait ClientProviderAwareTrait
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
     * @param ClientProviderInterface $service
     *
     * @return $this
     */
    public function setClientProvider(ClientProviderInterface $service)
    {
        return $this->setService('clientProvider', $service);
    }
    /**
     * @return ClientProviderInterface
     */
    public function getClientProvider()
    {
        return $this->getService('clientProvider');
    }
}