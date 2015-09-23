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

use Symfony\Component\HttpFoundation\RequestStack;

/**
 * RequestStackAware trait.
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
trait RequestStackAwareTrait
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
     * @param RequestStack $service
     *
     * @return $this
     */
    public function setRequestStack(RequestStack $service)
    {
        return $this->setService('requestStack', $service);
    }
    /**
     * @return RequestStack
     */
    public function getRequestStack()
    {
        return $this->getService('requestStack');
    }
}
