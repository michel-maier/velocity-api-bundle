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

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Velocity\Bundle\ApiBundle\Traits\ServiceAwareController;

/**
 * Rest Controller
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
abstract class AbstractRestController extends AbstractController
{
    use ServiceAwareController\RequestServiceAwareControllerTrait;
    use ServiceAwareController\ExceptionServiceAwareControllerTrait;
    /**
     * Returns the http response.
     *
     * @param mixed   $data
     * @param int     $code
     * @param array   $headers
     * @param array   $options
     * @param Request $request
     *
     * @return Response
     */
    protected function returnResponse($data = null, $code = 200, $headers = [], $options = [], Request $request = null)
    {
        return $this->get('velocity.response')->create(
            isset($request) && count($request->getAcceptableContentTypes()) ? $request->getAcceptableContentTypes() : [['value' => 'application/json']],
            $data,
            $code,
            $headers,
            $options
        );
    }
}
