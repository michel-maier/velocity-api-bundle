<?php

/*
 * This file is part of the VELOCITY package.
 *
 * (c) PHPPRO <opensource@phppro.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Velocity\Bundle\ApiBundle\Tests\DependencyInjection\Compiler;

use PHPUnit_Framework_TestCase;
use PHPUnit_Framework_MockObject_MockObject;
use Symfony\Component\HttpKernel\KernelInterface;
use Velocity\Bundle\ApiBundle\Service\VelocityService;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Velocity\Bundle\ApiBundle\DependencyInjection\Compiler\VelocityCompilerPass;

/**
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
class VelocityCompilerPassTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var VelocityCompilerPass
     */
    protected $p;
    /**
     * @var KernelInterface
     */
    protected $kernel;
    /**
     * @var VelocityService|PHPUnit_Framework_MockObject_MockObject
     */
    protected $velocity;

    public function setUp()
    {
        $this->velocity = $this->getMock('Velocity\\Bundle\\ApiBundle\\Service\\VelocityService', ['load'], [], '', false);
        $this->kernel   = $this->getMock('Symfony\\Component\\HttpKernel\\Kernel', [], [], '', false);
        $this->p        = new VelocityCompilerPass($this->kernel, $this->velocity);
    }
    /**
     * @group unit
     */
    public function testProcess()
    {
        $container = new ContainerBuilder();

        $this->velocity->expects($this->once())->method('load')->with($container, $this->kernel);

        $this->p->process($container);
    }
}
