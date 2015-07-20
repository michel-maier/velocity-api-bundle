<?php

/*
 * This file is part of the VELOCITY package.
 *
 * (c) PHPPRO <opensource@phppro.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Velocity\Bundle\ApiBundle\Service;

use DateTime;
use Velocity\Bundle\ApiBundle\Traits\ServiceTrait;
use Velocity\Bundle\ApiBundle\Traits\SecurityContextAwareTrait;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * Supervision Service.
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
class SupervisionService
{
    use ServiceTrait;
    use SecurityContextAwareTrait;
    /**
     * Construct a new service.
     *
     * @param SecurityContextInterface $securityContext
     */
    public function __construct(SecurityContextInterface $securityContext)
    {
        $this->setSecurityContext($securityContext);
    }
    /**
     * Returns useful information for Ping request.
     *
     * @return array
     */
    public function ping()
    {
        return [
            'date'          => new Datetime(),
            'startDuration' => defined('APP_TIME_START')
                ? (microtime(true) - constant('APP_TIME_START'))
                : null,
            'php' => [
                'version'   => PHP_VERSION,
                'os'        => PHP_OS,
                'versionId' => PHP_VERSION_ID,
            ],
            'hostName' => gethostname(),
        ];
    }
    /**
     * Returns the current logged in user identity.
     *
     * @return TokenInterface
     */
    public function getIdentity()
    {
        return $this->getSecurityContext()->getToken();
    }
}