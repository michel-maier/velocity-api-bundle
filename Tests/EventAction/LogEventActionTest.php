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

use Psr\Log\LoggerInterface;
use PHPUnit_Framework_TestCase;
use PHPUnit_Framework_MockObject_MockObject;
use Velocity\Bundle\ApiBundle\EventAction\Context;
use Velocity\Bundle\ApiBundle\EventAction\LogEventAction;

/**
 * @author Olivier Hoareau <olivier@phppro.fr>
 * @group eventAction
 * @group log
 */
class LogEventActionTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var LogEventAction
     */
    protected $ea;
    /**
     * @var Context|PHPUnit_Framework_MockObject_MockObject
     */
    protected $context;
    /**
     * @var LoggerInterface|PHPUnit_Framework_MockObject_MockObject
     */
    protected $logger;
    /**
     *
     */
    public function setUp()
    {
        $this->context = $this->getMock('Velocity\\Bundle\\ApiBundle\\EventAction\\Context', ['getCurrentEventName', 'getParameter'], [], '', false);
        $this->logger  = $this->getMock('Psr\\Log\\LoggerInterface');
        $this->ea      = new LogEventAction($this->logger);

        $this->ea->setContext($this->context);
    }
    /**
     * @group unit
     */
    public function testLog()
    {
        $this->context
            ->expects($this->once())
            ->method('getCurrentEventName')
            ->will($this->returnValue('myEvent'))
        ;

        $this->context
            ->expects($this->any())
            ->method('getVariable')
            ->will($this->returnValue(null))
        ;

        $this->logger
            ->expects($this->once())
            ->method('log')
            ->with('DEBUG', 'event [myEvent]')
        ;

        $this->ea->log();
    }
}
