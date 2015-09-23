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

use Velocity\Core\Traits\ServiceTrait;
use Velocity\Bundle\ApiBundle\Traits\ServiceAware;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Http\Firewall\ListenerInterface;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * API Listener.
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
class ApiListener implements ListenerInterface
{
    use ServiceTrait;
    use ServiceAware\RequestServiceAwareTrait;
    /**
     * Construct the listener.
     *
     * @param TokenStorageInterface          $tokenStorage
     * @param AuthenticationManagerInterface $authenticationManager
     */
    public function __construct(TokenStorageInterface $tokenStorage, AuthenticationManagerInterface $authenticationManager)
    {
        $this->setService('tokenStorage', $tokenStorage);
        $this->setService('authenticationManager', $authenticationManager);
    }
    /**
     * Handle the response.
     *
     * @param GetResponseEvent $event
     *
     * @return void
     */
    public function handle(GetResponseEvent $event)
    {
        /** @var TokenStorageInterface $tokenStorage */
        $tokenStorage = $this->getService('tokenStorage');

        /** @var AuthenticationManagerInterface $authenticationManager */
        $authenticationManager = $this->getService('authenticationManager');

        $tokenStorage->setToken(
            $authenticationManager->authenticate(
                new ApiUnauthenticatedUserToken(
                    $this->getRequestService()->parse($event->getRequest())
                )
            )
        );
    }
}
