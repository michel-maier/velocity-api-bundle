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

use Psr\Log\LoggerInterface;
use PHPUnit_Framework_TestCase;
use PHPUnit_Framework_MockObject_MockObject;
use Velocity\Bundle\ApiBundle\Action\LogAction;
use Velocity\Bundle\ApiBundle\Bag;

/**
 * @author Olivier Hoareau <olivier@phppro.fr>
 * @group eventAction
 * @group log
 */
class LogActionTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var LogAction
     */
    protected $ea;
    /**
     * @var LoggerInterface|PHPUnit_Framework_MockObject_MockObject
     */
    protected $logger;
    /**
     *
     */
    public function setUp()
    {
        $this->logger = $this->getMock('Psr\\Log\\LoggerInterface');
        $this->ea     = new LogAction($this->logger);
    }
    /**
     * @group unit
     */
    public function testLog()
    {
        $this->logger
            ->expects($this->once())
            ->method('log')
            ->with('DEBUG', 'event [myEvent]')
        ;

        $this->ea->log(new Bag(['message' => 'this is the message']), new Bag(['eventName' => 'myEvent']));
    }
}
