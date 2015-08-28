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
use Velocity\Bundle\ApiBundle\Service\BusinessRuleService;

/**
 * @author Olivier Hoareau <olivier@phppro.fr>
 *
 * @group businessRule
 */
class BusinessRuleServiceTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var BusinessRuleService
     */
    protected $s;
    /**
     *
     */
    public function setUp()
    {
        $this->s = new BusinessRuleService();
    }
    /**
     * @group unit
     */
    public function testAddBusinessRuleForUnknownTypeThrowException()
    {
        $brX001 = function () {

        };

        $this->setExpectedException('RuntimeException', "Unsupported business rule type for code 'X001'", 500);
        $this->s->addBusinessRule('X001', $brX001);

        $this->assertEquals(['callback' => $brX001, 'code' => 'X001', 'params' => []], $this->s->getBusinessRuleByCode('X001'));
    }
    /**
     * @group unit
     */
    public function testExecuteModelOperationBusinessRulesExecuteAllBusinessRulesInRegisteredOrder()
    {
        $context = (object) ['counter' => 0, 'value' => 0];

        $brX001 = function () use ($context) {
            $context->counter++;
            $context->value += 1;
        };
        $brX002 = function () use ($context) {
            $context->counter++;
            $context->value /= 2;
        };

        $this->s->addBusinessRule('X001', $brX001, ['model' => 'myModel', 'operation' => 'create']);
        $this->s->addBusinessRule('X002', $brX002, ['model' => 'myModel', 'operation' => 'create']);

        $this->s->executeBusinessRulesForModelOperation('myModel', 'create', (object) []);

        $this->assertEquals(2, $context->counter);
        $this->assertEquals(0.5, $context->value);
    }
}