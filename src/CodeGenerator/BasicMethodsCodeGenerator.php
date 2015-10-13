<?php

/*
 * This file is part of the VELOCITY package.
 *
 * (c) PHPPRO <opensource@phppro.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Velocity\Bundle\ApiBundle\CodeGenerator;

use Zend\Code\Generator\MethodGenerator;
use Velocity\Bundle\ApiBundle\Annotation as Velocity;

/**
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
class BasicMethodsCodeGenerator
{
    /**
     * @param MethodGenerator $zMethod
     * @param array           $definition
     *
     * @Velocity\CodeGeneratorMethodType("basic")
     */
    public function generateBasicMethod(MethodGenerator $zMethod, array $definition = [])
    {
        unset($zMethod, $definition);
    }
    /**
     * @param MethodGenerator $zMethod
     * @param array           $definition
     *
     * @Velocity\CodeGeneratorMethodType("get")
     */
    public function generateGetMethod(MethodGenerator $zMethod, $definition = [])
    {
        unset($definition);

        $zMethod->setParameters([]);
    }
}
