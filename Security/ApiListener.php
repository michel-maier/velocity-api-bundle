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

use Velocity\Bundle\ApiBundle\Traits\ServiceTrait;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Velocity\Bundle\ApiBundle\Traits\RequestServiceAwareTrait;
use Symfony\Component\Security\Http\Firewall\ListenerInterface;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;

/**
 * API Listener.
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
class ApiListener implements ListenerInterface
{
    use ServiceTrait;
    use RequestServiceAwareTrait;
    /**
     * Construct the listener.
     *
     * @param SecurityContextInterface       $securityContext
     * @param AuthenticationManagerInterface $authenticationManager
     */
    public function __construct(
        SecurityContextInterface $securityContext,
        AuthenticationManagerInterface $authenticationManager
    )
    {
        $this->setService('securityContext', $securityContext);
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
        /** @var SecurityContextInterface $securityContext */
        $securityContext = $this->getService('securityContext');

        /** @var AuthenticationManagerInterface $authenticationManager */
        $authenticationManager = $this->getService('authenticationManager');

        $securityContext->setToken(
            $authenticationManager->authenticate(
                new ApiUnauthenticatedUserToken(
                    $this->getRequestService()->parse($event->getRequest())
                )
            )
        );
    }
}