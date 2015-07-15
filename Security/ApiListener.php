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
    use RequestServiceAwareTrait;
    use ServiceTrait;
    /**
     * @var SecurityContextInterface
     */
    protected $securityContext;
    /**
     * @var AuthenticationManagerInterface
     */
    protected $authenticationManager;
    /**
     * @param SecurityContextInterface       $securityContext
     * @param AuthenticationManagerInterface $authenticationManager
     */
    public function __construct(
        SecurityContextInterface $securityContext, AuthenticationManagerInterface $authenticationManager
    )
    {
        $this->securityContext       = $securityContext;
        $this->authenticationManager = $authenticationManager;
    }
    /**
     * @param GetResponseEvent $event
     */
    public function handle(GetResponseEvent $event)
    {
        $this->securityContext->setToken(
            $this->authenticationManager->authenticate(
                new ApiUnauthenticatedUserToken($this->getRequestService()->parse($event->getRequest()))
            )
        );
    }
}