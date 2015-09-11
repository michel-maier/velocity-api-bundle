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
use Velocity\Bundle\ApiBundle\Service\VolatileSubSubDocumentService;

/**
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
class VolatileSubSubDocumentServiceTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var VolatileSubSubDocumentService
     */
    protected $s;
    /**
     *
     */
    public function setUp()
    {
        $this->s = new VolatileSubSubDocumentService();
    }
    /**
     * @group unit
     * @group document
     */
    public function testGetTypes()
    {
        $this->s->setTypes(['a', 'b', 'c']);
        $this->assertEquals(['a', 'b', 'c'], $this->s->getTypes());
    }
    /**
     * @group unit
     * @group document
     */
    public function testFullType()
    {
        $this->s->setTypes(['a', 'b', 'c']);
        $this->assertEquals('a.b.c', $this->s->getFullType());

        $this->s->setTypes(['a', 'b', 'c']);
        $this->assertEquals('a b c', $this->s->getFullType(' '));
    }
}
