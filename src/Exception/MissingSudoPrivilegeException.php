<?php

/*
 * This file is part of the VELOCITY package.
 *
 * (c) PHPPRO <opensource@phppro.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Velocity\Bundle\ApiBundle\Exception;

use Symfony\Component\Security\Core\Exception\AuthenticationException;

/**
 * MissingSudoPrivilegeException.
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
class MissingSudoPrivilegeException extends AuthenticationException
{
    /**
     * Construct the exception
     */
    public function __construct()
    {
        parent::__construct("Missing sudo privilege", 403);
    }
}