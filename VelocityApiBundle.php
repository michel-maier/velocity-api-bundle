<?php

/*
 * This file is part of the VELOCITY package.
 *
 * (c) PHPPRO <opensource@phppro.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Velocity\Bundle\ApiBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Velocity\Bundle\ApiBundle\DependencyInjection\Compiler\TagCompilerPass;
use Velocity\Bundle\ApiBundle\DependencyInjection\Security\Factory\ApiFactory;
use Velocity\Bundle\ApiBundle\DependencyInjection\Compiler\AnnotationCompilerPass;

/**
 * Velocity Api Bundle.
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
class VelocityApiBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $extension = $container->getExtension('security');
        /** @noinspection PhpUndefinedMethodInspection */
        $extension->addSecurityListenerFactory(new ApiFactory());

        $container->addCompilerPass(new AnnotationCompilerPass());
        $container->addCompilerPass(new TagCompilerPass());
    }
}
