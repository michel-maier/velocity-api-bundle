<?php

/*
 * This file is part of the VELOCITY package.
 *
 * (c) PHPPRO <opensource@phppro.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Velocity\Bundle\ApiBundle\Controller\Base;

use JMS\Serializer\SerializationContext;
use Symfony\Component\HttpFoundation\Response;
use Velocity\Bundle\ApiBundle\Traits\ServiceAwareController;

/**
 * Rest Controller
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
abstract class AbstractRestController extends AbstractController
{
    use ServiceAwareController\SerializerAwareControllerTrait;
    use ServiceAwareController\RequestServiceAwareControllerTrait;
    use ServiceAwareController\ExceptionServiceAwareControllerTrait;
    /**
     * Returns the http response.
     *
     * @param mixed $data
     * @param int   $code
     * @param array $headers
     * @param array $options
     *
     * @return Response
     */
    protected function returnResponse($data = null, $code = 200, $headers = [], $options = [])
    {
        $context = SerializationContext::create();

        if (isset($options['groups'])) {
            $context->setGroups($options['groups']);
        }

        return new Response(
            $this->getSerializer()->serialize($data, 'json', $context),
            $code,
            $headers + ['Content-Type' => 'application/json; charset=UTF-8']
        );
    }
}
