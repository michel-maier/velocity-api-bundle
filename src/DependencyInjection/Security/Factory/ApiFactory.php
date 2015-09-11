<?php

/*
 * This file is part of the VELOCITY package.
 *
 * (c) PHPPRO <opensource@phppro.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Velocity\Bundle\ApiBundle\DependencyInjection\Security\Factory;

use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Bundle\SecurityBundle\DependencyInjection\Security\Factory\SecurityFactoryInterface;

/**
 * Api Factory.
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
class ApiFactory implements SecurityFactoryInterface
{
    /**
     * Create a new API Factory.
     *
     * @param ContainerBuilder $container
     * @param string           $id
     * @param mixed            $config
     * @param string           $userProvider
     * @param mixed            $defaultEntryPoint
     *
     * @return array
     */
    public function create(ContainerBuilder $container, $id, $config, $userProvider, $defaultEntryPoint)
    {
        $providerId = 'velocity.security.authentication.provider.api.'.$id;
        $container
            ->setDefinition($providerId, new DefinitionDecorator('velocity.security.authentication.provider'))
            ->replaceArgument(0, new Reference($userProvider))
        ;

        $listenerId = 'velocity.security.authentication.listener.api.'.$id;
        $container->setDefinition($listenerId, new DefinitionDecorator('velocity.security.authentication.listener'));

        return array($providerId, $listenerId, $defaultEntryPoint);
    }
    /**
     * Return the position.
     *
     * @return string
     */
    public function getPosition()
    {
        return 'pre_auth';
    }
    /**
     * Return the key.
     *
     * @return string
     */
    public function getKey()
    {
        return 'api';
    }
    /**
     * @param NodeDefinition $node
     *
     * @return void
     */
    public function addConfiguration(NodeDefinition $node)
    {
    }
}
