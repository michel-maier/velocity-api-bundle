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

use Psr\Log\LoggerInterface;

/**
 * LoggerAware trait.
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
trait LoggerAwareTrait
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
     * @param LoggerInterface $service
     *
     * @return $this
     */
    public function setLogger(LoggerInterface $service)
    {
        return $this->setService('logger', $service);
    }
    /**
     * @return LoggerInterface
     */
    public function getLogger()
    {
        return $this->getService('logger');
    }
    /**
     * @param string $msg
     * @param string $level
     *
     * @return $this
     */
    protected function log($msg, $level = 'debug')
    {
        $this->getLogger()->log($level, call_user_func_array('sprintf', func_get_args()));

        return $this;
    }
}