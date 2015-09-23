<?php

/*
 * This file is part of the VELOCITY package.
 *
 * (c) PHPPRO <opensource@phppro.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Velocity\Bundle\ApiBundle\Listener;

use Velocity\Core\Traits\ServiceTrait;
use Velocity\Core\Traits\RequestStackAwareTrait;
use Symfony\Component\HttpFoundation\RequestStack;
use Velocity\Bundle\ApiBundle\Traits\ServiceAware;
use Velocity\Bundle\ApiBundle\Service\ResponseService;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;

/**
 * Exception Listener.
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
class ExceptionListener
{
    use ServiceTrait;
    use RequestStackAwareTrait;
    use ServiceAware\ResponseServiceAwareTrait;
    /**
     * @param ResponseService $responseService
     * @param RequestStack    $requestStack
     */
    public function __construct(ResponseService $responseService, RequestStack $requestStack)
    {
        $this->setResponseService($responseService);
        $this->setRequestStack($requestStack);
    }
    /**
     * Kernel exception event callback.
     *
     * @param GetResponseForExceptionEvent $event
     *
     * @return void
     */
    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $event->setResponse(
            $this->getResponseService()->createFromException(
                $this->getRequestStack()->getMasterRequest()->getAcceptableContentTypes(),
                $event->getException()
            )
        );
    }
}
