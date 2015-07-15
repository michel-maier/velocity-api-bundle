<?php

/*
 * This file is part of the VELOCITY package.
 *
 * (c) PHPPRO <opensource@phppro.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Velocity\Bundle\ApiBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Velocity\Bundle\ApiBundle\Service\RequestService;
use Velocity\Bundle\ApiBundle\Controller\Base\RestController;
use /** @noinspection PhpUnusedAliasInspection */ Nelmio\ApiDocBundle\Annotation\ApiDoc;
use /** @noinspection PhpUnusedAliasInspection */ Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use /** @noinspection PhpUnusedAliasInspection */ Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

/**
 * Security management controller.
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
class SecurityController extends RestController
{
    /**
     * @return RequestService
     */
    protected function getRequestService()
    {
        return $this->get('api.request');
    }
    /**
     * @Route("/client-tokens", name="api_client_token_create")
     * @Method({"POST"})
     *
     * @ApiDoc(
     *  section="Security",
     *  description="Create a new client token",
     *  statusCodes={
     *    204="Created"
     *  },
     *  views = { "security" }
     * )
     *
     * @param Request $request
     *
     * @return Response
     */
    public function createClientTokenAction(Request $request)
    {
        return $this->returnResponse(
            null,
            204,
            $this->getRequestService()->createClientTokenFromRequestAndReturnHeaders($request)
        );
    }
    /**
     * @Route("/user-tokens", name="api_user_token_create")
     * @Method({"POST"})
     *
     * @ApiDoc(
     *  section="Security",
     *  description="Create a new user token",
     *  statusCodes={
     *    204="Created"
     *  },
     *  views = { "security" }
     * )
     *
     * @param Request $request
     *
     * @return Response
     */
    public function createUserTokenAction(Request $request)
    {
        return $this->returnResponse(
            null,
            204,
            $this->getRequestService()->createUserTokenFromRequestAndReturnHeaders($request)
        );
    }
}