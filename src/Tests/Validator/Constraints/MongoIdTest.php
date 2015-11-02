<?php

/*
 * This file is part of the VELOCITY package.
 *
 * (c) PHPPRO <opensource@phppro.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Velocity\Bundle\ApiBundle\Tests\Validator\Constraints;

use PHPUnit_Framework_TestCase;
use Velocity\Bundle\ApiBundle\Validator\Constraints\MongoId;

/**
 * @author Olivier Hoareau <olivier@phppro.fr>
 *
 * @group constraints
 * @group validator
 */
class MongoIdTest extends PHPUnit_Framework_TestCase
{
    /**
     * @group unit
     */
    public function testConstruct()
    {
        $v = new MongoId();

        $this->assertNotNull($v);
        $this->assertEquals('velocity_mongo_id', $v->validatedBy());
    }
}
