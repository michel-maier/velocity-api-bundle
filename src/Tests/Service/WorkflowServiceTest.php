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
use Velocity\Bundle\ApiBundle\WorkflowInterface;
use Velocity\Bundle\ApiBundle\Service\WorkflowService;

/**
 * @author Olivier Hoareau <olivier@phppro.fr>
 *
 * @group workflow
 */
class WorkflowServiceTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var WorkflowService
     */
    protected $s;
    /**
     * @var BusinessRuleService|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $businessRule;
    /**
     *
     */
    public function setUp()
    {
        $this->businessRule = $this->getMock('Velocity\\Bundle\\ApiBundle\\Service\\BusinessRuleService', [], [], '', false);
        $this->s            = new WorkflowService($this->businessRule);
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
    public function testRegisterFromDefinition()
    {
        $this->assertFalse($this->s->has('workflow1'));
        $this->s->registerFromDefinition('workflow1', []);
        $this->assertTrue($this->s->has('workflow1'));

        $workflow = $this->s->get('workflow1');

        $this->assertTrue($workflow instanceof WorkflowInterface);
    }
    /**
     * @group unit
     */
    public function testHasTransition()
    {
        $this->s->registerFromDefinition('w', ['steps' => ['s1', 's2', 's3'], 'transitions' => ['s1' => ['s2'], 's2' => ['s3', 's1']]]);

        $this->assertTrue($this->s->hasTransition('w', 's1', 's2'));
        $this->assertFalse($this->s->hasTransition('w', 's1', 's3'));
        $this->assertFalse($this->s->hasTransition('w', 's1', 'sX'));
        $this->assertTrue($this->s->hasTransition('w', 's2', 's3'));
        $this->assertTrue($this->s->hasTransition('w', 's2', 's1'));
        $this->assertFalse($this->s->hasTransition('w', 's3', 's1'));
    }
    /**
     * @group unit
     */
    public function testTransition()
    {
        $this->businessRule->expects($this->exactly(2))->method('executeBusinessRulesForModelOperation');

        $this->s->registerFromDefinition('w', ['steps' => ['s1', 's2', 's3'], 'transitions' => ['s1' => ['s2'], 's2' => ['s3', 's1']]]);

        $docBefore = new \stdClass();
        $docBefore->status = 's1';

        $doc = new \stdClass();
        $doc->status = 's2';

        $this->s->transitionModelProperty('m', $doc, 'status', $docBefore, 'w');
    }
}
