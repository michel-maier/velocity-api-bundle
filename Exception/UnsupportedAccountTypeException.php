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
 * UnsupportedAccountTypeException.
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
class UnsupportedAccountTypeException extends AuthenticationException
{
    /**
     * @param string $type
     */
    public function __construct($type)
    {
        parent::__construct(sprintf("Unsupported account type '%s'", $type), 403);
    }
}