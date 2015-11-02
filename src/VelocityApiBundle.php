<?php

/*
 * This file is part of the VELOCITY package.
 *
 * (c) PHPPRO <opensource@phppro.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Velocity\Bundle\ApiBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Doctrine\Common\Annotations\AnnotationReader;
use Symfony\Component\HttpKernel\KernelInterface;
use Velocity\Bundle\ApiBundle\Service\VelocityService;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Bundle\SecurityBundle\DependencyInjection\SecurityExtension;
use Velocity\Bundle\ApiBundle\DependencyInjection\Security\Factory\ApiFactory;
use Velocity\Bundle\ApiBundle\DependencyInjection\Compiler\VelocityCompilerPass;

/**
 * Velocity Api Bundle.
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
class VelocityApiBundle extends Bundle
{
    /**
     * @var KernelInterface
     */
    protected $kernel;
    /**
     * @var ApiFactory
     */
    protected $apiFactory;
    /**
     * @var VelocityService
     */
    protected $velocityService;
    /**
     * @param KernelInterface $kernel
     * @param ApiFactory      $apiFactory
     * @param VelocityService $velocityService
     */
    public function __construct(KernelInterface $kernel, $apiFactory = null, $velocityService = null)
    {
        if (null === $apiFactory) {
            $apiFactory = new ApiFactory();
        }

        if (null === $velocityService) {
            $velocityService = new VelocityService(new AnnotationReader());
        }

        $this->kernel          = $kernel;
        $this->apiFactory      = $apiFactory;
        $this->velocityService = $velocityService;
    }
    /**
     * @return ApiFactory
     */
    public function getApiFactory()
    {
        return $this->apiFactory;
    }
    /**
     * @return KernelInterface
     */
    public function getKernel()
    {
        return $this->kernel;
    }
    /**
     * @return VelocityService
     */
    public function getVelocityService()
    {
        return $this->velocityService;
    }
    /**
     * @param ContainerBuilder $container
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        if ($container->hasExtension('security')) {
            $extension = $container->getExtension('security');
            /** @var SecurityExtension $extension */
            $extension->addSecurityListenerFactory($this->getApiFactory());
        }

        $container->addCompilerPass(new VelocityCompilerPass($this->getKernel(), $this->getVelocityService()));
    }
}
