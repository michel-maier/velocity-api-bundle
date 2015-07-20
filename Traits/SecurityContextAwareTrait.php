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

use Symfony\Component\Security\Core\SecurityContextInterface;

/**
 * SecurityContextAware trait.
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
trait SecurityContextAwareTrait
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
     * @param SecurityContextInterface $securityContextInterface
     *
     * @return $this
     */
    public function setSecurityContext(SecurityContextInterface $securityContextInterface)
    {
        return $this->setService('securityContext', $securityContextInterface);
    }
    /**
     * @return SecurityContextInterface
     */
    public function getSecurityContext()
    {
        return $this->getService('securityContext');
    }
}