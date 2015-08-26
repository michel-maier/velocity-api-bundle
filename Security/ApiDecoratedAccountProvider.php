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

use Exception;
use Velocity\Bundle\ApiBundle\Traits\ServiceTrait;

/**
 * API Decorated Account Provider.
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
class ApiDecoratedAccountProvider
{
    use ServiceTrait;
    /**
     * @param mixed  $accountProvider
     * @param string $method
     *
     * @throws Exception
     */
    public function __construct($accountProvider, $method = 'get')
    {
        if (!method_exists($accountProvider, $method)) {
            throw $this->createException(
                500,
                "Missing method %s::%s()",
                get_class($accountProvider),
                $method
            );
        }

        $this->setParameter('accountProvider', $accountProvider);
        $this->setParameter('method', $method);
    }
    /**
     * Return the specified account.
     *
     * @param string $id
     *
     * @return mixed
     */
    public function get($id)
    {
        return $this->getParameter('accountProvider')->{$this->getParameter('method')}($id);
    }
}
