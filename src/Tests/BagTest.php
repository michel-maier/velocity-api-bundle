<?php

/*
 * This file is part of the VELOCITY package.
 *
 * (c) PHPPRO <opensource@phppro.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Velocity\Bundle\ApiBundle\Tests;

use PHPUnit_Framework_TestCase;
use Velocity\Bundle\ApiBundle\Bag;

/**
 * @author Olivier Hoareau <olivier@phppro.fr>
 * @group eventAction
 */
class BagTest extends PHPUnit_Framework_TestCase
{
    /**
     * @group unit
     */
    public function testGetVariables()
    {
        $b = new Bag(['g' => 'h']);

        $this->assertTrue($b->has('g'));
        $this->assertFalse($b->has('i'));

        $this->assertEquals('h', $b->get('g'));
    }
}
