<?php

/*
 * This file is part of the VELOCITY package.
 *
 * (c) PHPPRO <opensource@phppro.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Velocity\Bundle\ApiBundle\Tests\Service;

use PHPUnit_Framework_TestCase;
use PHPUnit_Framework_MockObject_MockObject;
use Velocity\Bundle\ApiBundle\Service\GeneratorService;
use Velocity\Bundle\ApiBundle\Service\CallableService;

/**
 * @author Olivier Hoareau <olivier@phppro.fr>
 *
 * @group generator
 */
class GeneratorServiceTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var GeneratorService
     */
    protected $s;
    /**
     * @var CallableService|PHPUnit_Framework_MockObject_MockObject
     */
    protected $callableService;
    /**
     *
     */
    public function setUp()
    {
        $this->callableService = $this->getMock("Velocity\\Bundle\\ApiBundle\\Service\\CallableService", [], [], '', false);
        $this->s = new GeneratorService($this->callableService);
    }
    /**
     * @group unit
     */
    public function testConstruct()
    {
        $this->assertNotNull($this->s);
    }
    /**
     * @group unit
     */
    public function testRegister()
    {
        $callback = function () {
        };

        $this->callableService
            ->expects($this->once())
            ->method('registerByType')
            ->will($this->returnValue($this->callableService))
            ->with('generator', 'test', $callback)
        ;

        $this->s->register('test', $callback);

        $this->callableService
            ->expects($this->once())
            ->method('getByType')
            ->will($this->returnValue(['type' => 'callable', 'callable' => $callback, 'options' => []]))
            ->with('generator', 'test')
        ;

        $this->assertEquals(['type' => 'callable', 'callable' => $callback, 'options' => []], $this->s->get('test'));
    }
}
