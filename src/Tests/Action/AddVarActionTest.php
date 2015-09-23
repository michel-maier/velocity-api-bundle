<?php

/*
 * This file is part of the VELOCITY package.
 *
 * (c) PHPPRO <opensource@phppro.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Velocity\Bundle\ApiBundle\Tests\Action;

use PHPUnit_Framework_TestCase;
use Velocity\Core\Bag;
use PHPUnit_Framework_MockObject_MockObject;
use Velocity\Bundle\ApiBundle\Action\AddVarAction;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @author Olivier Hoareau <olivier@phppro.fr>
 * @group eventAction
 */
class AddVarActionTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var AddVarAction
     */
    protected $ea;
    /**
     * @var ContainerInterface|PHPUnit_Framework_MockObject_MockObject
     */
    protected $container;
    /**
     *
     */
    public function setUp()
    {
        $this->container = $this->getMock('Symfony\\Component\\DependencyInjection\\Container', [], [], '', false);
        $this->ea = new AddVarAction($this->container);
    }
    /**
     * @group unit
     * @group attachment
     */
    public function testExecute()
    {
        $c = new AddVarActionTestExecuteTestClass();

        $this->container->expects($this->once())->method('get')->with('s1')->will($this->returnValue($c));
        $this->ea->execute(
            new Bag(['method' => 'm1', 'service' => 's1', 'name' => 'v1']),
            new Bag(['event' => new GenericEvent(), 'eventName' => 'e1'])
        );

        $this->assertTrue($c->called);
    }
}
