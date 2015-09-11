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
     * @param array  $params
     *
     * @return \Exception
     */
    protected abstract function createException($code, $msg, ...$params);
    /**
     * @param string $name
     * @param array  $args
     *
     * @throws \Exception
     */
    public function __call($name, $args)
    {
        unset($args);

        throw $this->createException(500, 'Unknown method %s::%s()', get_class($this), $name);
    }
}
