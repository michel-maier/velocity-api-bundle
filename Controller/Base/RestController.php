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

use JMS\Serializer\SerializerInterface;
use JMS\Serializer\SerializationContext;
use Symfony\Component\HttpFoundation\Response;
use Velocity\Bundle\ApiBundle\Service\RequestService;
use Velocity\Bundle\ApiBundle\Service\ExceptionService;

/**
 * Rest Controller
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
abstract class RestController extends BaseController
{
    /**
     * Return the serializer (json).
     *
     * @return SerializerInterface
     */
    protected function getSerializer()
    {
        return $this->get('jms_serializer');
    }
    /**
     * Return the exception service.
     *
     * @return ExceptionService
     */
    protected function getExceptionService()
    {
        return $this->get('api.exception');
    }
    /**
     * @return RequestService
     */
    protected function getRequestService()
    {
        return $this->get('api.request');
    }
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
            $headers
            +
            [
                'Content-Type' => 'application/json; charset=UTF-8',
            ]
        );
    }
}