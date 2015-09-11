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
use Velocity\Bundle\ApiBundle\Service\JobService;

/**
 * @author Olivier Hoareau <olivier@phppro.fr>
 *
 * @group job
 */
class JobServiceTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var JobService
     */
    protected $s;
    /**
     *
     */
    public function setUp()
    {
        $this->s = new JobService();
    }
    /**
     * @group unit
     * @group generator
     */
    public function testAdd()
    {
        $callback = function () {
        };

        $this->assertEquals([], $this->s->find());
        $this->s->add('test', $callback);
        $this->assertEquals(['test' => ['callable' => $callback, 'options' => []]], $this->s->find());
        $this->assertEquals(['callable' => $callback, 'options' => []], $this->s->get('test'));
    }
}
