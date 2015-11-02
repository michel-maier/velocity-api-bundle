<?php

/*
 * This file is part of the VELOCITY package.
 *
 * (c) PHPPRO <opensource@phppro.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Velocity\Bundle\ApiBundle\Tests;

use PHPUnit_Framework_TestCase;
use Symfony\Bundle\SecurityBundle\DependencyInjection\SecurityExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Velocity\Bundle\ApiBundle\VelocityApiBundle;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * @author Olivier Hoareau <olivier@phppro.fr>
 *
 * @group velocityapibundle
 */
class VelocityApiBundleTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var VelocityApiBundle
     */
    protected $b;
    /**
     * @var KernelInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $kernel;
    /**
     *
     */
    public function setUp()
    {
        $this->kernel = $this->getMock('Symfony\\Component\\HttpKernel\\KernelInterface', [], [], '', false);
        $this->b      = new VelocityApiBundle($this->kernel);
    }
    /**
     * @group unit
     */
    public function testConstruct()
    {
        $b = new VelocityApiBundle($this->kernel);

        $this->assertNotNull($b);
    }
    /**
     * @group integ
     */
    public function testBuild()
    {
        $containerBuilder = new ContainerBuilder();

        /** @var SecurityExtension|\PHPUnit_Framework_MockObject_MockObject $extension */
        $extension = $this->getMock('Symfony\\Bundle\\SecurityBundle\\DependencyInjection\\SecurityExtension', ['addSecurityListenerFactory', 'getAlias'], [], '', false);

        $extension->expects($this->once())->method('getAlias')->will($this->returnValue('security'));
        $extension->expects($this->once())->method('addSecurityListenerFactory')->with($this->b->getApiFactory());

        $containerBuilder->registerExtension($extension);
        $previousPassCount = count($containerBuilder->getCompilerPassConfig()->getPasses());

        $this->b->build($containerBuilder);

        $this->assertEquals($previousPassCount + 1, count($containerBuilder->getCompilerPassConfig()->getPasses()));
    }
}
