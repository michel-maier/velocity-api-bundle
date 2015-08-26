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

use Velocity\Bundle\ApiBundle\Service\PasswordService;

/**
 * PasswordServiceAware trait.
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
trait PasswordServiceAwareTrait
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
     * @return PasswordService
     */
    public function getPasswordService()
    {
        return $this->getService('password');
    }
    /**
     * @param PasswordService $service
     *
     * @return $this
     */
    public function setPasswordService(PasswordService $service)
    {
        return $this->setService('password', $service);
    }
}
