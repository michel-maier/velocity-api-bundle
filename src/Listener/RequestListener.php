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
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

/**
 * Request Listener.
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
class RequestListener
{
    use ServiceTrait;
    /**
     * Kernel request event callback.
     *
     * @param GetResponseEvent $event
     *
     * @return void
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();

        if ('json' !== $request->getContentType()) {
            return;
        }

        $data = json_decode($request->getContent(), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $event->setResponse(new JsonResponse(
                [
                    'code'    => 412,
                    'status'  => 'exception',
                    'message' => 'Malformed data (json syntax)',
                ],
                412
            ));
        } elseif (is_array($data)) {
            $request->request->replace($data);
        }
    }
}
