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
use Velocity\Bundle\ApiBundle\Service\ActionService;

/**
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
class ActionServiceTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var ActionService
     */
    protected $s;
    /**
     *
     */
    public function setUp()
    {
        $this->s = new ActionService();
    }
    /**
     * @group unit
     */
    public function testConstruct()
    {
        $this->assertNotNull($this->s);
    }
}
