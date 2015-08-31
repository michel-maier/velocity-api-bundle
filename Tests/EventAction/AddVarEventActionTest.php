<?php

/*
 * This file is part of the VELOCITY package.
 *
 * (c) PHPPRO <opensource@phppro.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Velocity\Bundle\ApiBundle\Tests\EventAction;

use PHPUnit_Framework_TestCase;
use PHPUnit_Framework_MockObject_MockObject;
use Symfony\Component\EventDispatcher\GenericEvent;
use Velocity\Bundle\ApiBundle\EventAction\AddVarEventAction;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Velocity\Bundle\ApiBundle\EventAction\Context;

/**
 * @author Olivier Hoareau <olivier@phppro.fr>
 * @group eventAction
 */
class AddVarEventActionTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var AddVarEventAction
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
        $this->ea = new AddVarEventAction($this->container);
    }
    /**
     * @group unit
     * @group attachment
     */
    public function testExecute()
    {
        $this->ea->execute(new Context(new GenericEvent(), 'event1'));
    }
}
