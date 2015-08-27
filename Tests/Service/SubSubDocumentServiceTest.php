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
use Velocity\Bundle\ApiBundle\Service\SubSubDocumentService;

/**
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
class SubSubDocumentServiceTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var SubSubDocumentService
     */
    protected $s;
    /**
     *
     */
    public function setUp()
    {
        $this->s = new SubSubDocumentService();
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
    /**
     * @group unit
     * @group document
     */
    public function testGetRepoKey()
    {
        $this->s->setTypes(['x', 'y', 'z']);

        $this->assertEquals('xs.a.ys.b.zs', $this->s->getRepoKey(['a', 'b']));
    }
}
