<?php

namespace Velocity\Bundle\ApiBundle\Traits;

trait MissingMethodCatcherTrait
{
    /**
     * @param int    $code
     * @param string $msg
     *
     * @return void
     */
    protected abstract function throwException($code, $msg);
    /**
     * @param string $name
     * @param array  $args
     *
     * @throws \RuntimeException
     */
    public function __call($name, $args)
    {
        $this->throwException(500, 'Unknown method %s::%s()', get_class($this), $name);
    }
}