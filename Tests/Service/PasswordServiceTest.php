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
use Velocity\Bundle\ApiBundle\Service\PasswordService;

/**
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
class PasswordServiceTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var PasswordService
     */
    protected $s;

    public function setUp()
    {
        $this->s = new PasswordService();
    }
    /**
     * @group unit
     */
    public function testTestForInvalidEncryptedValuesReturnFalse()
    {
        $this->assertFalse($this->s->test('test', null));
        $this->assertFalse($this->s->test('test', ''));
        $this->assertFalse($this->s->test('test', '0'));
        $this->assertFalse($this->s->test('test', 0));
        $this->assertFalse($this->s->test('test', -1));
        $this->assertFalse($this->s->test('test', false));
        $this->assertFalse($this->s->test('test', 'abcd'));
    }
    /**
     * @group unit
     */
    public function testTestForValidEncryptedValueReturnTrue()
    {
        $this->assertTrue($this->s->test('test', 'b80464c61eb6916aa003892772872b8092941cd5'));
        $this->assertTrue($this->s->test('test', 'fa42590735891ca591e8e38d9772f1bd1f337fbf', ['salt' => 'thisisthesalt']));
    }
    /**
     * @group unit
     */
    public function testGenerateForSpecialDataReturnSpecialRawPassword()
    {
        $this->assertEquals('9896abb2', $this->s->generate(['email' => 'a+test@b.com']));
    }
    /**
     * @group unit
     */
    public function testGenerateForStandardDataReturnPasswordGeneratedWithDefaultAlgorithm()
    {
        $password = $this->s->generate(['email' => 'a@b.com']);

        $this->assertEquals(6, strlen($password));
        $this->assertTrue(0 < preg_match('/^[a-z]{3}[A-Z]{2}[0-9]{1}$/', $password));
    }
    /**
     * @group unit
     */
    public function testGenerateForUnknownGeneratorThrowException()
    {
        $this->setExpectedException('RuntimeException', "Unsupported generator 'unknown'", 412);
        $this->s->generate([], ['generator' => 'unknown']);
    }
    /**
     * @group unit
     */
    public function testEncryptForUnknownAlgorithmThrowException()
    {
        $this->setExpectedException('RuntimeException', "Unsupported algorithm 'unknown'", 412);
        $this->s->encrypt('test', ['algorithm' => 'unknown']);
    }
}
