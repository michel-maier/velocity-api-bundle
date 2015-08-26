<?php

/*
 * This file is part of the VELOCITY package.
 *
 * (c) PHPPRO <opensource@phppro.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Velocity\Bundle\ApiBundle\DependencyInjection\Compiler;

use Symfony\Component\HttpKernel\KernelInterface;
use Velocity\Bundle\ApiBundle\Annotation\Refresh;
use Velocity\Bundle\ApiBundle\Service\VelocityService;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

/**
 * Velocity Compiler Pass.
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
class VelocityCompilerPass implements CompilerPassInterface
{
    /**
     * @var KernelInterface
     */
    private $kernel;
    /**
     * @var VelocityService
     */
    private $velocity;
    /**
     * @param KernelInterface $kernel
     * @param VelocityService $velocity
     */
    public function __construct(KernelInterface $kernel, VelocityService $velocity)
    {
        $this->kernel   = $kernel;
        $this->velocity = $velocity;
    }
    /**
     * Process the compiler pass.
     *
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        $this->velocity->load($container, $this->kernel);
    }
}
