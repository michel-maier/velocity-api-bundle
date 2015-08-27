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
use Velocity\Bundle\ApiBundle\Service\SubDocumentService;

/**
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
class SubDocumentServiceTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var SubDocumentService
     */
    protected $s;
    /**
     *
     */
    public function setUp()
    {
        $this->s = new SubDocumentService();
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
    /**
     * @group unit
     * @group document
     */
    public function testGetRepoKey()
    {
        $this->s->setTypes(['x', 'y']);

        $this->assertEquals('xs.a.ys', $this->s->getRepoKey(['a']));
        $this->assertEquals('xs.a.ys.b', $this->s->getRepoKey(['a', 'b']));
        $this->assertEquals('xs.a.ys.b', $this->s->getRepoKey(['a', 'b', 'c']));
        $this->assertEquals('xs.unknown.ys', $this->s->getRepoKey());

        $this->assertEquals('a.ys', $this->s->getRepoKey(['a'], ['skip' => 1]));
        $this->assertEquals('a', $this->s->getRepoKey(['a'], ['skip' => 2]));
    }
}
