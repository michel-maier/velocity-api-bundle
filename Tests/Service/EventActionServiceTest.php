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
use Velocity\Bundle\ApiBundle\Service\EventActionService;

/**
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
class EventActionServiceTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var EventActionService
     */
    protected $s;
    /**
     *
     */
    public function setUp()
    {
        $this->s = new EventActionService();
    }
    /**
     * @group unit
     */
    public function testAddEventActionForUnknownTypeThrowException()
    {
        $this->setExpectedException('RuntimeException', "No event action registered for 'unknown'", 412);
        $this->s->addEventAction('test.event', 'unknown');
    }
}
