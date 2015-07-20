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
 * TranslatedExceptionThrower trait.
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
trait TranslatedExceptionThrowerTrait
{
    use ExceptionThrowerTrait;
    /**
     * @param int    $code
     * @param string $msg
     * @param array  $params
     *
     * @throws \RuntimeException
     *
     * @return mixed
     */
    protected function throwTranslatedException($code, $msg, $params = [])
    {
        $args = func_get_args();

        $code = array_shift($args);

        if (method_exists($this, 'translate')) {
            $method = [$this, 'translate'];
            foreach($args as $i => $arg) {
                $args[$i] = $this->translate($arg);
            }
        } else {
            $method = 'sprintf';
        }

        $this->throwException($code, call_user_func_array($method, $args));
    }
}