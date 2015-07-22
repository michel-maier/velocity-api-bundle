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

use Velocity\Bundle\ApiBundle\Service\ExceptionService;

/**
 * ExceptionServiceAware trait.
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
trait ExceptionServiceAwareTrait
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
     * @param ExceptionService $service
     *
     * @return $this
     */
    public function setExceptionService(ExceptionService $service)
    {
        return $this->setService('exception', $service);
    }
    /**
     * @return ExceptionService
     */
    public function getExceptionService()
    {
        return $this->getService('exception');
    }
}