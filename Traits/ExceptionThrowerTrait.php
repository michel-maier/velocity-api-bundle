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
 * ExceptionThrower trait.
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
trait ExceptionThrowerTrait
{
    /**
     * @param int    $code
     * @param string $msg
     * @param array  $params
     *
     * @throws \Exception
     *
     * @return mixed
     */
    protected function createException($code, $msg, ...$params)
    {
        throw new \RuntimeException(call_user_func_array('sprintf', array_merge([$msg], $params)), $code);
    }
}
