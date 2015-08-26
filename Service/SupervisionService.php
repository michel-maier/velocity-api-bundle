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
use Velocity\Bundle\ApiBundle\Traits\TokenStorageAwareTrait;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Supervision Service.
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
class SupervisionService
{
    use ServiceTrait;
    use TokenStorageAwareTrait;
    /**
     * Construct a new service.
     *
     * @param TokenStorageInterface $tokenStorage
     */
    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->setTokenStorage($tokenStorage);
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
        return $this->getTokenStorage()->getToken();
    }
}
