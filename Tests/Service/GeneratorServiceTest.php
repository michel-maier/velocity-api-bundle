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
use Velocity\Bundle\ApiBundle\Service\GeneratorService;

/**
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
class GeneratorServiceTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var GeneratorService
     */
    protected $s;

    public function setUp()
    {
        $this->s = new GeneratorService();
    }
    /**
     * @group unit
     * @group generator
     */
    public function testRegister()
    {
        $callback = function () {};

        $this->assertEquals([], $this->s->getGenerators());
        $this->s->register('test', $callback);
        $this->assertEquals(['test' => ['callable' => $callback, 'options' => []]], $this->s->getGenerators());
        $this->assertEquals(['callable' => $callback, 'options' => []], $this->s->getGeneratorByName('test'));
    }
}
