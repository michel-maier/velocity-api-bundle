<?php

/*
 * This file is part of the VELOCITY package.
 *
 * (c) PHPPRO <opensource@phppro.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Velocity\Bundle\ApiBundle\Security;

/**
 * API Decorated Account Provider.
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
class ApiDecoratedAccountProvider
{
    /**
     * @var mixed
     */
    protected $method;
    /**
     * @var string
     */
    protected $accountProvider;
    /**
     * @param mixed  $accountProvider
     * @param string $method
     */
    public function __construct($accountProvider, $method = 'get')
    {
        if (!method_exists($accountProvider, $method)) {
            throw new \RuntimeException(sprintf("Missing method %s::%s()", get_class($accountProvider), $method), 500);
        }

        $this->accountProvider = $accountProvider;
        $this->method          = $method;
    }
    /**
     * @param string $id
     *
     * @return mixed
     */
    public function get($id)
    {
        return $this->accountProvider->{$this->method}($id);
    }
}