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
use Velocity\Bundle\ApiBundle\Service\InvitationEventService;

/**
 * @author Olivier Hoareau <olivier@phppro.fr>
 *
 * @group invitationEvent
 */
class InvitationEventServiceTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var InvitationEventService
     */
    protected $s;
    /**
     *
     */
    public function setUp()
    {
        $this->s = new InvitationEventService();
    }
    /**
     * @group unit
     */
    public function testExecuteTypeTransitionInvitationEventsExecuteAllInvitationEventsInRegisteredOrder()
    {
        $context = (object) ['counter' => 0, 'value' => 0];

        $a = function () use ($context) {
            $context->counter++;
            $context->value += 1;
        };

        $b = function () use ($context) {
            $context->counter++;
            $context->value /= 2;
        };

        $c = function () use ($context) {
            $context->counter++;
            $context->value /= 2;
        };

        $this->s->addInvitationEvent('type1', 'transition1', $a);
        $this->s->addInvitationEvent('type1', 'transition1', $b);
        $this->s->addInvitationEvent('type2', 'transition2', $c);

        $this->s->executeInvitationEventsForTypeTransition('type1', 'transition1', (object) []);

        $this->assertEquals(2, $context->counter);
        $this->assertEquals(0.5, $context->value);
    }
}
