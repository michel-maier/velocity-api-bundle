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

use Velocity\Bundle\ApiBundle\Traits\ServiceTrait;
use Velocity\Bundle\ApiBundle\Service\ExceptionService;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;

/**
 * Exception Listener.
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
class ExceptionListener
{
    use ServiceTrait;
    /**
     * @param ExceptionService $exceptionService
     *
     * @return $this
     */
    public function setExceptionService(ExceptionService $exceptionService)
    {
        return $this->setService('exception', $exceptionService);
    }
    /**
     * @return ExceptionService
     */
    public function getExceptionService()
    {
        return $this->getService('exception');
    }
    /**
     * @param ExceptionService $exceptionService
     */
    public function __construct(ExceptionService $exceptionService)
    {
        $this->setExceptionService($exceptionService);
    }
    /**
     * @param GetResponseForExceptionEvent $event
     */
    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $event->setResponse($this->getExceptionService()->convertToResponse($event->getException()));
    }
}