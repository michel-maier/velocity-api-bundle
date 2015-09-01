<?php

/*
 * This file is part of the VELOCITY package.
 *
 * (c) PHPPRO <opensource@phppro.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Velocity\Bundle\ApiBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

/**
 * Configuration.
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
class Configuration implements ConfigurationInterface
{
    /**
     * @return TreeBuilder
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('velocity_api');

        $this
            ->addFrontSection($rootNode)
            ->addSendersSection($rootNode)
            ->addModelsSection($rootNode)
            ->addRecipientsSection($rootNode)
            ->addEventsSection($rootNode)
        ;

        return $treeBuilder;
    }
    /**
     * @param ArrayNodeDefinition $rootNode
     *
     * @return $this
     */
    protected function addFrontSection(ArrayNodeDefinition $rootNode)
    {
        $rootNode
            ->children()
                ->arrayNode('front')
                    ->isRequired()
                    ->children()
                        ->scalarNode('name')->end()
                        ->scalarNode('url')->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $this;
    }
    /**
     * @param ArrayNodeDefinition $rootNode
     *
     * @return $this
     */
    protected function addSendersSection(ArrayNodeDefinition $rootNode)
    {
        $rootNode
            ->children()
                ->arrayNode('senders')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('sender')->end()
                            ->scalarNode('name')->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $this;
    }
    /**
     * @param ArrayNodeDefinition $rootNode
     *
     * @return $this
     */
    protected function addModelsSection(ArrayNodeDefinition $rootNode)
    {
        $rootNode
            ->children()
                ->arrayNode('models')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('bundles')
                            ->prototype('scalar')
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $this;
    }
    /**
     * @param ArrayNodeDefinition $rootNode
     *
     * @return $this
     */
    protected function addRecipientsSection(ArrayNodeDefinition $rootNode)
    {
        $rootNode
            ->beforeNormalization()
                ->always(function ($v) {
                    return $v + ['recipients' => []];
                })
            ->end()
            ->children()
                ->arrayNode('recipients')
                    ->prototype('array')
                        ->prototype('array')
                            ->beforeNormalization()
                                ->always(function ($v) {
                                    if (!is_array($v)) {
                                        $v = [];
                                    }
                                    if (!isset($v['envs'])) {
                                        $v += ['envs' => ['*']];
                                    }
                                    if (!isset($v['types'])) {
                                        $v += ['types' => ['*']];
                                    }

                                    return $v;
                                })
                            ->end()
                            ->children()
                                ->scalarNode('name')->end()
                                ->arrayNode('envs')
                                    ->requiresAtLeastOneElement()
                                    ->prototype('scalar')->end()
                                ->end()
                                ->arrayNode('types')
                                    ->requiresAtLeastOneElement()
                                    ->prototype('scalar')->end()
                                ->end()
                            ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $this;
    }
    /**
     * @param ArrayNodeDefinition $rootNode
     *
     * @return $this
     */
    protected function addEventsSection(ArrayNodeDefinition $rootNode)
    {
        $rootNode
            ->beforeNormalization()
                ->always(function ($v) {
                    return $v + ['events' => []];
                })
            ->end()
            ->children()
                ->arrayNode('events')
                    ->prototype('array')
                        ->children()
                            ->arrayNode('actions')
                                ->prototype('array')
                                    ->beforeNormalization()
                                        ->always(function ($v) {
                                            if (!is_array($v)) {
                                                return [];
                                            }
                                            if (!isset($v['action'])) {
                                                return ['params' => $v];
                                            }
                                            $action = $v['action'];
                                            unset($v['action']);

                                            return ['action' => $action, 'params' => $v];
                                        })
                                    ->end()
                                    ->children()
                                        ->scalarNode('action')->end()
                                        ->variableNode('params')->defaultValue([])->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $this;
    }
}
