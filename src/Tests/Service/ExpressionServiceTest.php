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
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\Templating\EngineInterface;
use Velocity\Bundle\ApiBundle\Service\ExpressionService;

/**
 * @author Olivier Hoareau <olivier@phppro.fr>
 *
 * @group expression
 */
class ExpressionServiceTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var ExpressionService
     */
    protected $s;
    /**
     * @var EngineInterface|PHPUnit_Framework_MockObject_MockObject
     */
    protected $templatingService;
    /**
     * @var ExpressionLanguage|PHPUnit_Framework_MockObject_MockObject
     */
    protected $expressionLanguage;
    /**
     *
     */
    public function setUp()
    {
        $this->templatingService  = $this->getMock("Symfony\\Component\\Templating\\EngineInterface", [], [], '', false);
        $this->expressionLanguage = $this->getMock("Symfony\\Component\\ExpressionLanguage\\ExpressionLanguage", [], [], '', false);
        $this->s = new ExpressionService($this->templatingService, $this->expressionLanguage);
    }
    /**
     * @group unit
     */
    public function testConstruct()
    {
        $this->assertNotNull($this->s);
    }
    /**
     * @group integ
     */
    public function testEvaluateExpressionLanguage()
    {
        $this->s->setExpressionLanguage(new ExpressionLanguage());

        $vars = ['a' => [1, 2], 'b' => 5, 'c' => 2];

        $this->assertEquals([1, 2], $this->s->evaluate('$a', $vars));
        $this->assertEquals(5, $this->s->evaluate('$b', $vars));
        $this->assertEquals(2, $this->s->evaluate('$c', $vars));

        $this->assertEquals(2.5, $this->s->evaluate('$ (b + c - 2) / 2', $vars));
    }
}
