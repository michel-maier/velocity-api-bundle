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
     *
     * @throws \RuntimeException
     *
     * @return mixed
     */
    protected function throwException($code, $msg)
    {
        $args = func_get_args();

        $code = array_shift($args);

        if (method_exists($this, 'translate')) {
            $pattern = array_shift($args);
            if (1 === count($args) && is_array($args[0])) {
                $args = array_shift($args);
            }
            $msg = $this->translate($pattern, $args);
            array_unshift($args, $msg);
        }

        throw new \RuntimeException(ucfirst(call_user_func_array('sprintf', $args)), $code);
    }
}