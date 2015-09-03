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
use Velocity\Bundle\ApiBundle\EventAction\Context;
use Velocity\Bundle\ApiBundle\EventAction\AuditLogEventAction;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @author Olivier Hoareau <olivier@phppro.fr>
 * @group eventAction
 * @group auditLog
 */
class AuditLogEventActionTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var AuditLogEventAction
     */
    protected $ea;
    /**
     * @var Context|PHPUnit_Framework_MockObject_MockObject
     */
    protected $context;
    /**
     * @var TokenStorageInterface|PHPUnit_Framework_MockObject_MockObject
     */
    protected $tokenStorage;
    /**
     * @var EventDispatcherInterface|PHPUnit_Framework_MockObject_MockObject
     */
    protected $eventDispatcher;
    /**
     *
     */
    public function setUp()
    {
        $this->context         = $this->getMock('Velocity\\Bundle\\ApiBundle\\EventAction\\Context', [], [], '', false);
        $this->tokenStorage    = $this->getMock('Symfony\\Component\\Security\\Core\\Authentication\\Token\\Storage\\TokenStorageInterface');
        $this->eventDispatcher = $this->getMock('Symfony\\Component\\EventDispatcher\\EventDispatcher', ['dispatch'], [], '', false);
        $this->ea = new AuditLogEventAction($this->tokenStorage, $this->eventDispatcher);

        $this->ea->setContext($this->context);
    }
    /**
     * @group unit
     */
    public function testAuditLog()
    {
        $this->eventDispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->with('audit.log')
        ;

        $this->tokenStorage
            ->expects($this->once())
            ->method('getToken')
            ->will($this->returnValue(null))
        ;

        $this->ea->auditLog();
    }
}
