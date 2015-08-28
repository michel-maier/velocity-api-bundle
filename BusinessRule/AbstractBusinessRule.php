<?php

/*
 * This file is part of the VELOCITY package.
 *
 * (c) PHPPRO <opensource@phppro.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Velocity\Bundle\ApiBundle\BusinessRule;

use Velocity\Bundle\ApiBundle\Traits\ServiceTrait;
use Velocity\Bundle\ApiBundle\Exception\BusinessRuleException;

/**
 * Abstract Business Rule.
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
abstract class AbstractBusinessRule
{
    use ServiceTrait;
    /**
     * @param string $msg
     * @param array  $params
     *
     * @throws BusinessRuleException
     *
     * @return mixed
     */
    protected function restricted($msg, ...$params)
    {
        throw new BusinessRuleException(call_user_func_array('sprintf', array_merge([$msg], $params)), 403);
    }
    /**
     * @param string $msg
     * @param array  $params
     *
     * @throws BusinessRuleException
     *
     * @return mixed
     */
    protected function denied($msg, ...$params)
    {
        throw new BusinessRuleException(call_user_func_array('sprintf', array_merge([$msg], $params)), 403);
    }
    /**
     * @param string $msg
     * @param array  $params
     *
     * @throws BusinessRuleException
     *
     * @return mixed
     */
    protected function blocked($msg, ...$params)
    {
        throw new BusinessRuleException(call_user_func_array('sprintf', array_merge([$msg], $params)), 403);
    }
}
