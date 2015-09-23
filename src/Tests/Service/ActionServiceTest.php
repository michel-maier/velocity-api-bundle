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
use Symfony\Component\Templating\EngineInterface;
use Velocity\Bundle\ApiBundle\Service\ActionService;
use Velocity\Bundle\ApiBundle\Service\CallableService;

/**
 * @author Olivier Hoareau <olivier@phppro.fr>
 *
 * @group action
 */
class ActionServiceTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var ActionService
     */
    protected $s;
    /**
     * @var CallableService|PHPUnit_Framework_MockObject_MockObject
     */
    protected $callableService;
    /**
     * @var EngineInterface|PHPUnit_Framework_MockObject_MockObject
     */
    protected $templatingService;
    /**
     *
     */
    public function setUp()
    {
        $this->callableService   = $this->getMock("Velocity\\Bundle\\ApiBundle\\Service\\CallableService", [], [], '', false);
        $this->templatingService = $this->getMock("Symfony\\Component\\Templating\\EngineInterface", [], [], '', false);
        $this->s = new ActionService($this->callableService, $this->templatingService);
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
            ->with('action', 'test', $callback)
        ;

        $this->s->register('test', $callback);

        $this->callableService
            ->expects($this->once())
            ->method('getByType')
            ->will($this->returnValue(['type' => 'callable', 'callable' => $callback, 'options' => []]))
            ->with('action', 'test')
        ;

        $this->assertEquals(['type' => 'callable', 'callable' => $callback, 'options' => []], $this->s->get('test'));
    }
}
