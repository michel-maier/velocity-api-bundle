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
use Velocity\Bundle\ApiBundle\Traits\ExceptionServiceAwareTrait;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;

/**
 * Exception Listener.
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
class ExceptionListener
{
    use ServiceTrait;
    use ExceptionServiceAwareTrait;
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
            $this->getExceptionService()->convertToResponse($event->getException())
        );
    }
}
