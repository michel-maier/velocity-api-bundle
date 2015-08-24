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
    private $kernel;
    /**
     * @param KernelInterface $kernel
     */
    public function __construct(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }
    /**
     * @param ContainerBuilder $container
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $extension = $container->getExtension('security');
        /** @noinspection PhpUndefinedMethodInspection */
        $extension->addSecurityListenerFactory(new ApiFactory());

        $velocity = new VelocityService(new AnnotationReader());

        $container->addCompilerPass(new VelocityCompilerPass($this->kernel, $velocity));
    }
}
