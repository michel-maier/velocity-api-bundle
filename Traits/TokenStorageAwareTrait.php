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

use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * TokenStorageAware trait.
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
trait TokenStorageAwareTrait
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
     * @param TokenStorageInterface $tokenStorageInterface
     *
     * @return $this
     */
    public function setTokenStorage(TokenStorageInterface $tokenStorageInterface)
    {
        return $this->setService('tokenStorage', $tokenStorageInterface);
    }
    /**
     * @return TokenStorageInterface
     */
    public function getTokenStorage()
    {
        return $this->getService('tokenStorage');
    }
}
