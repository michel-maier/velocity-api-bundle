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
use Velocity\Bundle\ApiBundle\Service\DocumentService;

/**
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
class DocumentServiceTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var DocumentService
     */
    protected $s;
    /**
     *
     */
    public function setUp()
    {
        $this->s = new DocumentService();
    }
    /**
     * @group unit
     * @group document
     */
    public function testGetTypes()
    {
        $this->s->setTypes(['a']);
        $this->assertEquals(['a'], $this->s->getTypes());
    }
    /**
     * @group unit
     * @group document
     */
    public function testFullType()
    {
        $this->s->setTypes(['a']);
        $this->assertEquals('a', $this->s->getFullType());
    }
    /**
     * @group unit
     * @group document
     */
    public function testGetRepoKey()
    {
        $this->s->setTypes(['x']);

        $this->assertEquals('', $this->s->getRepoKey());
        $this->assertEquals('a', $this->s->getRepoKey(['a']));
    }
}
