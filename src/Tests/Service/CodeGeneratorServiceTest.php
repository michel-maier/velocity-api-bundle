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
use Zend\Code\Generator\MethodGenerator;
use Velocity\Bundle\ApiBundle\Service\CallableService;
use Velocity\Bundle\ApiBundle\Service\CodeGeneratorService;

/**
 * @author Olivier Hoareau <olivier@phppro.fr>
 *
 * @group codeGenerator
 */
class CodeGeneratorServiceTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var CodeGeneratorService
     */
    protected $s;
    /**
     * @var CallableService|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $callableService;
    /**
     *
     */
    public function setUp()
    {
        $this->callableService = $this->getMock("Velocity\\Bundle\\ApiBundle\\Service\\CallableService", [], [], '', false);
        $this->s = new CodeGeneratorService($this->callableService);
    }
    /**
     * @group unit
     */
    public function testConstruct()
    {
        $this->assertNotNull($this->s);
    }
    /**
     * @group unit
     */
    public function testCreateClassForEmptyClassReturnZendClassWithNoMethods()
    {
        $zClass = $this->s->createClass('MyTestClass');

        $this->assertEquals('MyTestClass', $zClass->getName());
        $this->assertEquals(0, count($zClass->getMethods()));
    }
    /**
     * @group unit
     */
    public function testCreateClassForOneMethodWithoutTypeReturnZendClassWithThisBasicMethod()
    {
        $zClass = $this->s->createClass(
            'MyTestClass',
            [
                'methods' => [
                    ['name' => 'method1'],
                ],
            ]
        );

        $this->assertEquals(1, count($zClass->getMethods()));
    }
    /**
     * @group integ
     */
    public function testCreateClassForMethodWithExistingTypeReturnZendClassWithBuiltMethod()
    {
        $this->s->setCallableService(new CallableService());

        $this->s->registerMethodType(
            'basic2',
            function (MethodGenerator $zMethod) {
                $zMethod->setBody("return 'this is generated';");
            }
        );

        $zClass = $this->s->createClass(
            'MyTestClass',
            [
                'methods' => [
                    'method1' => [],
                    'method2' => ['type' => 'basic2'],
                ],
            ]
        );

        $this->assertEquals(2, count($zClass->getMethods()));

        $zMethod = $zClass->getMethod('method2');

        $this->assertEquals("return 'this is generated';", $zMethod->getBody());
    }
    /**
     * @group integ
     */
    public function testCreateClassForMethodWithUnknownTypeThrowException()
    {
        $this->s->setCallableService(new CallableService());

        $this->setExpectedException('RuntimeException', "No 'iAmUnknown' in methodTypes list", 412);

        $this->s->createClass(
            'MyTestClass',
            [
                'methods' => [
                    'method1' => ['type' => 'iAmUnknown'],
                ],
            ]
        );
    }
}
