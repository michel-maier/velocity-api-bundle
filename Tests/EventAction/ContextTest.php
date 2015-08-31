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
use Velocity\Bundle\ApiBundle\EventAction\Context;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * @author Olivier Hoareau <olivier@phppro.fr>
 * @group eventAction
 */
class ContextTest extends PHPUnit_Framework_TestCase
{
    /**
     * @group unit
     */
    public function testGetVariables()
    {
        $event     = new GenericEvent((object) ['a' => 'b', 'c' => 'd']);
        $eventName = 'e1';
        $params    = ['e' => 'f'];

        $c = new Context($event, $eventName, $params, ['g' => 'h']);

        $this->assertEquals(
            [
                'eventName' => 'e1',
                'a'         => 'b',
                'c'         => 'd',
                'e'         => 'f',
                'g'         => 'h',
            ],
            $c->getVariables()
        );

        $this->assertEquals($event, $c->getEvent());
        $this->assertEquals($eventName, $c->getEventName());
        $this->assertEquals($params, $c->getParams());
    }
}
