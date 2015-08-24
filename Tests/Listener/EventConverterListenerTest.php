<?php

/*
 * This file is part of the VELOCITY package.
 *
 * (c) PHPPRO <opensource@phppro.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Velocity\Bundle\ApiBundle\Tests\Listener;

use PHPUnit_Framework_TestCase;
use Velocity\Bundle\ApiBundle\Event;
use PHPUnit_Framework_MockObject_MockObject;
use Symfony\Component\EventDispatcher\GenericEvent;
use Velocity\Bundle\ApiBundle\Listener\EventConverterListener;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
class EventConverterListenerTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var EventConverterListener
     */
    protected $l;
    /**
     * @var EventDispatcherInterface|PHPUnit_Framework_MockObject_MockObject
     */
    protected $eventDispatcher;

    public function setUp()
    {
        $this->eventDispatcher = $this->getMock('Symfony\\Component\\EventDispatcher\\EventDispatcher', [], [], '', false);
        $this->l = new EventConverterListener($this->eventDispatcher);
    }
    /**
     * @group unit
     */
    public function testMailUser()
    {
        $this->eventDispatcher->expects($this->once())->method('dispatch')->with('mail', new Event\Mail\UserMailEvent('test', ['subject' => 'test']));

        $this->l->mailUser(new GenericEvent('test'), 'test');
    }
    /**
     * @group unit
     */
    public function testMailAdmin()
    {
        $this->eventDispatcher->expects($this->once())->method('dispatch')->with('mail', new Event\Mail\AdminMailEvent('test', ['subject' => 'test']));

        $this->l->mailAdmin(new GenericEvent('test'), 'test');
    }
    /**
     * @group unit
     */
    public function testSmsUser()
    {
        $this->eventDispatcher->expects($this->once())->method('dispatch')->with('sms', new Event\Sms\UserSmsEvent('test', ['subject' => 'test']));

        $this->l->smsUser(new GenericEvent('test'), 'test');
    }
    /**
     * @group unit
     */
    public function testSmsAdmin()
    {
        $this->eventDispatcher->expects($this->once())->method('dispatch')->with('sms', new Event\Sms\AdminSmsEvent('test', ['subject' => 'test']));

        $this->l->smsAdmin(new GenericEvent('test'), 'test');
    }
    /**
     * @group unit
     */
    public function testFireAndForget()
    {
        $this->eventDispatcher->expects($this->once())->method('dispatch')->with('fireAndForget', new Event\FireAndForgetEvent('test', ['subject' => 'test']));

        $this->l->fireAndForget(new GenericEvent('test'), 'test');
    }
}
