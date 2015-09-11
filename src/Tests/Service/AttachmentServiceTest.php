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
use PHPUnit_Framework_MockObject_MockObject;
use Velocity\Bundle\ApiBundle\Service\GeneratorService;
use Velocity\Bundle\ApiBundle\Service\AttachmentService;

/**
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
class AttachmentServiceTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var AttachmentService
     */
    protected $s;
    /**
     * @var GeneratorService|PHPUnit_Framework_MockObject_MockObject
     */
    protected $generator;
    /**
     *
     */
    public function setUp()
    {
        $this->generator = $this->getMock('Velocity\\Bundle\\ApiBundle\\Service\\GeneratorService', ['generate'], [], '', false);
        $this->s = new AttachmentService($this->generator);
    }
    /**
     * @group unit
     * @group attachment
     */
    public function testBuild()
    {
        $this->generator->expects($this->once())->method('generate')->will($this->returnValue('result'));

        $this->assertEquals(
            ['name' => 'test.pdf', 'type' => 'application/pdf', 'content' => base64_encode('result')],
            $this->s->build(['name' => 'test.pdf', 'generator' => 'test'])
        );
    }
}
