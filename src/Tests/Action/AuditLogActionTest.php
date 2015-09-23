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
use Velocity\Bundle\ApiBundle\Action\AuditLogAction;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @author Olivier Hoareau <olivier@phppro.fr>
 * @group eventAction
 * @group auditLog
 */
class AuditLogActionTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var AuditLogAction
     */
    protected $ea;
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
        $this->tokenStorage    = $this->getMock('Symfony\\Component\\Security\\Core\\Authentication\\Token\\Storage\\TokenStorageInterface');
        $this->eventDispatcher = $this->getMock('Symfony\\Component\\EventDispatcher\\EventDispatcher', ['dispatch'], [], '', false);
        $this->ea = new AuditLogAction($this->tokenStorage, $this->eventDispatcher);
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

        $this->ea->auditLog(new Bag(['contextType' => 'a', 'contextId' => 'b']), new Bag());
    }
}
