<?php

/*
 * This file is part of the VELOCITY package.
 *
 * (c) PHPPRO <opensource@phppro.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Velocity\Bundle\ApiBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

/**
 * Tag Compiler Pass.
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
class TagCompilerPass implements CompilerPassInterface
{
    /**
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        $this->processRepositoryTag($container);
        $this->processCrudTag($container);
        $this->processSubCrudTag($container);
        $this->processProviderClientTag($container);
        $this->processProviderAccountTag($container);
    }
    /**
     * @param ContainerBuilder $container
     */
    protected function processRepositoryTag(ContainerBuilder $container)
    {
        $defaultClass   = 'Velocity\\Bundle\\ApiBundle\\Service\\RepositoryService';
        $defaultIdField = '_id';

        foreach ($container->findTaggedServiceIds('api.repository') as $id => $attributes) {
            $typeName = substr($id, strrpos($id, '.') + 1);
            $definition = $container->getDefinition($id);
            if (!$definition->getClass()) {
                $definition->setClass($defaultClass);
            }
            $tagAttribute = array_shift($attributes);
            $collectionName = isset($tagAttribute['collection']) ? $tagAttribute['collection'] : $typeName;
            $idField = isset($tagAttribute['idField']) ? $tagAttribute['idField'] : $defaultIdField;
            $definition->addMethodCall('setDatabaseService', [new Reference('api.database')]);
            $definition->addMethodCall('setTranslator', [new Reference('translator')]);
            $definition->addMethodCall('setEventDispatcher', [new Reference('event_dispatcher')]);
            $definition->addMethodCall('setLoggerService', [new Reference('logger')]);
            $definition->addMethodCall('setCollectionName', [$collectionName]);
            $definition->addMethodCall('setIdField', [$idField]);
        }
    }
    /**
     * @param ContainerBuilder $container
     */
    protected function processCrudTag(ContainerBuilder $container)
    {
        $defaultClass = 'Velocity\\Bundle\\ApiBundle\\Service\\DocumentService';

        foreach ($container->findTaggedServiceIds('api.crud') as $id => $attributes) {
            $typeName = substr($id, strrpos($id, '.') + 1);
            $definition = $container->getDefinition($id);
            if (!$definition->getClass()) {
                $definition->setClass($defaultClass);
            }
            $tagAttribute = array_shift($attributes);
            $type = isset($tagAttribute['type']) ? $tagAttribute['type'] : $typeName;
            $repositoryId = sprintf('app.repository.%s', $type);
            $definition->addMethodCall('setType', [$type]);
            $definition->addMethodCall('setRepository', [new Reference($repositoryId)]);
            $definition->addMethodCall('setFormService', [new Reference('api.form')]);
            $definition->addMethodCall('setMetaDataService', [new Reference('api.metadata')]);
            $definition->addMethodCall('setLoggerService', [new Reference('logger')]);
            $definition->addMethodCall('setEventDispatcher', [new Reference('event_dispatcher')]);
        }
    }
    /**
     * @param ContainerBuilder $container
     */
    protected function processSubCrudTag(ContainerBuilder $container)
    {
        $defaultClass = 'Velocity\\Bundle\\ApiBundle\\Service\\SubDocumentService';

        foreach ($container->findTaggedServiceIds('api.crud.sub') as $id => $attributes) {
            $tokens = explode('.', $id);
            $subTypeName = array_pop($tokens);
            $typeName = array_pop($tokens);
            $definition = $container->getDefinition($id);
            if (!$definition->getClass()) {
                $definition->setClass($defaultClass);
            }
            $tagAttribute = array_shift($attributes);
            $repositoryId = isset($tagAttribute['repo']) ? $tagAttribute['repo'] : (sprintf('app.repository.%s', $typeName));
            $definition->addMethodCall('setType', [$typeName]);
            $definition->addMethodCall('setSubType', [$subTypeName]);
            $definition->addMethodCall('setRepository', [new Reference($repositoryId)]);
            $definition->addMethodCall('setFormService', [new Reference('api.form')]);
            $definition->addMethodCall('setMetaDataService', [new Reference('api.metadata')]);
            $definition->addMethodCall('setLoggerService', [new Reference('logger')]);
            $definition->addMethodCall('setEventDispatcher', [new Reference('event_dispatcher')]);
        }
    }
    /**
     * @param ContainerBuilder $container
     */
    protected function processProviderAccountTag(ContainerBuilder $container)
    {
        $userProviderDefinition = $container->getDefinition('api.security.provider.user.api');

        foreach ($container->findTaggedServiceIds('api.provider.account') as $id => $attributes) {
            foreach($attributes as $attribute) {
                $type   = isset($attribute['type']) ? $attribute['type'] : 'default';
                $method = isset($attribute['method']) ? $attribute['method'] : 'get';
                $format = isset($attribute['format']) ? $attribute['format'] : 'plain';
                $userProviderDefinition->addMethodCall('setAccountProvider', [new Reference($id), $type, $method, $format]);
            }
        }
    }
    /**
     * @param ContainerBuilder $container
     */
    protected function processProviderClientTag(ContainerBuilder $container)
    {
        $authenticationProviderDefinition = $container->getDefinition('api.security.authentication.provider');

        foreach ($container->findTaggedServiceIds('api.provider.client') as $id => $attributes) {
            $authenticationProviderDefinition->addMethodCall('setClientService', [new Reference($id)]);
        }
    }
}