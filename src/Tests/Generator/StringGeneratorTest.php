<?php

/*
 * This file is part of the VELOCITY package.
 *
 * (c) PHPPRO <opensource@phppro.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Velocity\Bundle\ApiBundle\Tests\Generator;

use PHPUnit_Framework_TestCase;
use Velocity\Bundle\ApiBundle\Generator\StringGenerator;

/**
 * @author Olivier Hoareau <olivier@phppro.fr>
 *
 * @group generator
 */
class StringGeneratorTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var StringGenerator
     */
    protected $g;
    /**
     *
     */
    public function setUp()
    {
        $this->g = new StringGenerator();
    }
    /**
     * @group unit
     */
    public function testGenerateMd5()
    {
        $this->assertEquals(32, strlen($this->g->generateMd5String()));
        $this->assertTrue(0 < preg_match('/^[a-f0-9]+$/', $g = $this->g->generateMd5String()), sprintf("string '%s' is not valid md5 string", $g));
    }
    /**
     * @group unit
     */
    public function testGenerateSha1()
    {
        $this->assertEquals(40, strlen($this->g->generateSha1String()));
    }
}
