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

/**
 * MissingMethodCatcher trait.
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
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