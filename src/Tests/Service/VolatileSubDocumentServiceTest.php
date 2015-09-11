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
use Velocity\Bundle\ApiBundle\Service\VolatileSubDocumentService;

/**
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
class VolatileSubDocumentServiceTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var VolatileSubDocumentService
     */
    protected $s;
    /**
     *
     */
    public function setUp()
    {
        $this->s = new VolatileSubDocumentService();
    }
    /**
     * @group unit
     * @group document
     */
    public function testGetTypes()
    {
        $this->s->setTypes(['a', 'b']);
        $this->assertEquals(['a', 'b'], $this->s->getTypes());
    }
    /**
     * @group unit
     * @group document
     */
    public function testFullType()
    {
        $this->s->setTypes(['a', 'b']);
        $this->assertEquals('a.b', $this->s->getFullType());

        $this->s->setTypes(['a', 'b']);
        $this->assertEquals('a b', $this->s->getFullType(' '));
    }
}
