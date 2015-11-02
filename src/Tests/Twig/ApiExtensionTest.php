<?php

/*
 * This file is part of the VELOCITY package.
 *
 * (c) PHPPRO <opensource@phppro.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Velocity\Bundle\ApiBundle\Tests\Twig;

use PHPUnit_Framework_TestCase;
use Velocity\Bundle\ApiBundle\Twig\ApiExtension;

/**
 * @author Olivier Hoareau <olivier@phppro.fr>
 *
 * @group twig
 */
class ApiExtensionTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var ApiExtension
     */
    protected $e;
    /**
     *
     */
    public function setUp()
    {
        $this->e = new ApiExtension();
    }
    /**
     * @group unit
     */
    public function testConstruct()
    {
        $e = new ApiExtension(['a' => 'b', 'c' => 'd']);

        $this->assertNotNull($e);

        $this->assertEquals(['velocity' => ['a' => 'b', 'c' => 'd']], $e->getGlobals());
    }
    /**
     * @group unit
     */
    public function testGetFilters()
    {
        $this->assertNotEquals(0, count($this->e->getFilters()));
    }
    /**
     * @group unit
     */
    public function testGetBase64EncodedString()
    {
        $this->assertEquals(base64_encode('test'), $this->e->getBase64EncodedString('test'));
    }
    /**
     * @group unit
     */
    public function testGetName()
    {
        $this->assertEquals('velocity_api', $this->e->getName());
    }
}
