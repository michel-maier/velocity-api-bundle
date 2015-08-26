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

use Symfony\Component\HttpFoundation\Response;
use Velocity\Bundle\ApiBundle\Traits\ServiceAwareController;
use Velocity\Bundle\ApiBundle\Controller\Base\RestController;
use /** @noinspection PhpUnusedAliasInspection */ Nelmio\ApiDocBundle\Annotation\ApiDoc;
use /** @noinspection PhpUnusedAliasInspection */ Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use /** @noinspection PhpUnusedAliasInspection */ Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

/**
 * Supervision management controller.
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
class SupervisionController extends RestController
{
    use ServiceAwareController\SupervisionServiceAwareControllerTrait;
    /**
     * @Route("/supervision/ping", name="api_supervision_ping")
     * @Method({"GET"})
     *
     * @ApiDoc(
     *  section="Supervision",
     *  description="Ping",
     *  statusCodes={
     *    200="OK"
     *  },
     *  views = { "infra" }
     * )
     *
     * @return Response
     */
    public function pingAction()
    {
        return $this->returnResponse($this->getSupervisionService()->ping());
    }
    /**
     * @Route("/supervision/whoami", name="api_supervision_whoami")
     * @Method({"GET"})
     *
     * @ApiDoc(
     *  section="Supervision",
     *  description="Who am I",
     *  statusCodes={
     *    200="OK"
     *  },
     *  views = { "infra" }
     * )
     *
     * @return Response
     */
    public function whoamiAction()
    {
        return $this->returnResponse($this->getSupervisionService()->getIdentity());
    }
}
