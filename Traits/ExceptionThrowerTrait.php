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

use Exception;
use RuntimeException;

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
     * @throws Exception
     *
     * @return mixed
     */
    protected function createException($code, $msg, ...$params)
    {
        if (method_exists($this, 'translate')) {
            if (1 === count($params) && is_array($params[0])) {
                $params = array_shift($params);
            }
            $msg = $this->translate($msg, $params);
        } else {
            $msg = call_user_func_array('sprintf', array_merge([$msg], $params));
        }

        throw new RuntimeException($msg, $code);
    }
}