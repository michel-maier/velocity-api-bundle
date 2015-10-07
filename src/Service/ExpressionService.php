<?php

/*
 * This file is part of the VELOCITY package.
 *
 * (c) PHPPRO <opensource@phppro.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Velocity\Bundle\ApiBundle\Service;

use Velocity\Core\Traits\ServiceTrait;
use Velocity\Core\Traits\TemplatingAwareTrait;
use Symfony\Component\Templating\EngineInterface;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Velocity\Bundle\ApiBundle\Traits\ExpressionLanguageAwareTrait;

/**
 * Expression Service.
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
class ExpressionService
{
    use ServiceTrait;
    use TemplatingAwareTrait;
    use ExpressionLanguageAwareTrait;
    /**
     * @param EngineInterface    $templating
     * @param ExpressionLanguage $expressionLanguage
     */
    public function __construct(EngineInterface $templating, ExpressionLanguage $expressionLanguage)
    {
        $this->setTemplating($templating);
        $this->setExpressionLanguage($expressionLanguage);
    }
    /**
     * @param mixed $raw
     * @param mixed $vars
     *
     * @return mixed
     */
    public function evaluate($raw, &$vars)
    {
        if (is_array($raw)) {
            foreach ($raw as $k => $v) {
                unset($raw[$k]);
                $raw[$this->evaluate($k, $vars)] = $this->evaluate($v, $vars);
            }

            return $raw;
        }

        if (is_object($raw) || is_numeric($raw)) {
            return $raw;
        }

        if (is_string($raw)) {
            $matches = null;
            if (0 < preg_match('/^\$(.+)$/', $raw, $matches)) {
                return $this->getExpressionLanguage()->evaluate(trim($matches[1]), $vars);
            }

            return $this->getTemplating()->render('VelocityApiBundle::expression.txt.twig', ['_expression' => $raw] + $vars);
        }

        return $raw;
    }
}
