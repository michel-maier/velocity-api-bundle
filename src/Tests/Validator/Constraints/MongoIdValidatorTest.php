<?php

/*
 * This file is part of the VELOCITY package.
 *
 * (c) PHPPRO <opensource@phppro.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Velocity\Bundle\ApiBundle\Tests\Validator\Constraints;

use PHPUnit_Framework_TestCase;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Velocity\Bundle\ApiBundle\Validator\Constraints\MongoId;
use Velocity\Bundle\ApiBundle\Validator\Constraints\MongoIdValidator;

/**
 * @author Olivier Hoareau <olivier@phppro.fr>
 *
 * @group constraints
 * @group validator
 */
class MongoIdValidatorTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var ExecutionContextInterface|\PHPUnit_Framework_MockObject_MockObject $context
     */
    protected $context;
    /**
     * @var MongoIdValidator
     */
    protected $v;
    /**
     *
     */
    public function setUp()
    {
        $this->context = $this->getMock('Symfony\\Component\\Validator\\Context\\ExecutionContextInterface', [], [], '', false);
        $this->v       = new MongoIdValidator();

        $this->v->initialize($this->context);
    }
    /**
     * @group unit
     */
    public function testConstruct()
    {
        $this->assertNotNull(new MongoIdValidator());
    }
    /**
     * @group unit
     */
    public function testValidateForNull()
    {
        $this->context->expects($this->once())->method('addViolation')->with('Not a valid Mongo ID');
        $this->v->validate(null, new MongoId());
    }
    /**
     * @group unit
     */
    public function testValidateForMalformed()
    {
        $this->context->expects($this->once())->method('addViolation')->with('Not a valid Mongo ID');

        $this->v->validate('abc12', new MongoId());
    }
    /**
     * @group unit
     */
    public function testValidate()
    {
        $this->context->expects($this->never())->method('addViolation');

        $this->v->validate('aaaaaaaaaaaaaaaaaaaa0000', new MongoId());
    }
}
