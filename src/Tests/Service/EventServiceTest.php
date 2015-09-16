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
use Velocity\Bundle\ApiBundle\Service\EventService;
use Velocity\Bundle\ApiBundle\Service\ActionService;

/**
 * @author Olivier Hoareau <olivier@phppro.fr>
 *
 * @group event
 */
class EventServiceTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var EventService
     */
    protected $s;
    /**
     * @var ActionService|PHPUnit_Framework_MockObject_MockObject
     */
    protected $actionService;
    /**
     *
     */
    public function setUp()
    {
        $this->actionService = $this->getMock("Velocity\\Bundle\\ApiBundle\\Service\\ActionService", [], [], '', false);
        $this->s = new EventService($this->actionService);
    }
    /**
     * @group unit
     */
    public function testConstruct()
    {
        $this->assertNotNull($this->s);
    }
}
