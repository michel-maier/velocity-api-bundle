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
use Doctrine\Common\Annotations\AnnotationReader;
use Symfony\Component\DependencyInjection\Definition;
use Velocity\Bundle\ApiBundle\Service\VelocityService;

/**
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
class VelocityServiceTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var VelocityService
     */
    protected $s;

    public function setUp()
    {
        $this->s = new VelocityService(new AnnotationReader());
    }
    /**
     * @group unit
     * @group velocity
     */
    public function testModelClasses()
    {
        $d = new Definition();

        $this->s->loadClassesMetaData([
            'Velocity\\Bundle\\ApiBundle\\Tests\\Model\\Model1',
            'Velocity\\Bundle\\ApiBundle\\Tests\\Model\\Model2',
        ], $d);

        $this->assertEquals(
            [
                ['addModel', ['Velocity\\Bundle\\ApiBundle\\Tests\\Model\\Model1', []]],
                ['addModel', ['Velocity\\Bundle\\ApiBundle\\Tests\\Model\\Model2', []]],
            ],
            $d->getMethodCalls()
        );
    }
    /**
     * @group unit
     */
    public function testIsVelocityAnnotatedClass()
    {
        $this->assertTrue($this->s->isVelocityAnnotatedClass('Velocity\\Bundle\\ApiBundle\\Tests\\Model\\Model1'));
        $this->assertTrue($this->s->isVelocityAnnotatedClass('Velocity\\Bundle\\ApiBundle\\Tests\\Model\\Model2'));
        $this->assertFalse($this->s->isVelocityAnnotatedClass(__CLASS__));
    }
    /**
     * @group unit
     */
    public function testFindVelocityAnnotatedClassesInDirectory()
    {
        $classes = $this->s->findVelocityAnnotatedClassesInDirectory(__DIR__.'/../Model');

        $expected = [
            'Velocity\\Bundle\\ApiBundle\\Tests\\Model\\Model1',
            'Velocity\\Bundle\\ApiBundle\\Tests\\Model\\Model2',
        ];

        $this->assertEquals($expected, $classes);
        $this->assertEquals(count($expected), count($classes));
    }
}
