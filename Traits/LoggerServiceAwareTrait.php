<?php

namespace Velocity\Bundle\ApiBundle\Traits;

use Psr\Log\LoggerInterface;

trait LoggerServiceAwareTrait
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
    public function setLoggerService(LoggerInterface $service)
    {
        return $this->setService('logger', $service);
    }
    /**
     * @return LoggerInterface
     */
    public function getLoggerService()
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
        $this->getLoggerService()->log($level, call_user_func_array('sprintf', func_get_args()));

        return $this;
    }
}