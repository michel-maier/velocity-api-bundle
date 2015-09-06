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
use Velocity\Bundle\ApiBundle\Service\BatchService;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @author Olivier Hoareau <olivier@phppro.fr>
 *
 * @group batch
 */
class BatchServiceTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var BatchService
     */
    protected $s;
    /**
     * @var EventDispatcherInterface|PHPUnit_Framework_MockObject_MockObject
     */
    protected $eventDispatcher;
    /**
     *
     */
    public function setUp()
    {
        $this->eventDispatcher = $this->getMock('Symfony\\Component\\EventDispatcher\\EventDispatcher', ['dispatch'], [], '', false);
        $this->s = new BatchService($this->eventDispatcher);
    }
    /**
     * @group unit
     * @group generator
     */
    public function testConstruct()
    {
        $this->assertNotNull($this->s);
    }
}
