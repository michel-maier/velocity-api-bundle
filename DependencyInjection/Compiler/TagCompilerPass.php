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

use Doctrine\Common\Annotations\AnnotationReader;
use ReflectionClass;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Velocity\Bundle\ApiBundle\Annotation\Callback;

/**
 * Tag Compiler Pass.
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
class TagCompilerPass implements CompilerPassInterface
{
    /**
     * Process the compiler pass.
     *
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        $this->processRepositoryTag($container);
        $this->processCrudTag($container);
        $this->processSubCrudTag($container);
        $this->processVolatileTag($container);
        $this->processProviderClientTag($container);
        $this->processProviderAccountTag($container);
        $this->processMigratorTag($container);
    }
    /**
     * Process repository tags.
     *
     * @param ContainerBuilder $container
     */
    protected function processRepositoryTag(ContainerBuilder $container)
    {
        $defaultClass   = 'Velocity\\Bundle\\ApiBundle\\Service\\RepositoryService';

        foreach ($container->findTaggedServiceIds('api.repository') as $id => $attributes) {
            $typeName = substr($id, strrpos($id, '.') + 1);
            $definition = $container->getDefinition($id);
            if (!$definition->getClass()) {
                $definition->setClass($defaultClass);
            }
            $tagAttribute = array_shift($attributes);
            $collectionName = isset($tagAttribute['collection']) ? $tagAttribute['collection'] : $typeName;
            $definition->addMethodCall('setDatabaseService', [new Reference('api.database')]);
            $definition->addMethodCall('setTranslator', [new Reference('translator')]);
            $definition->addMethodCall('setEventDispatcher', [new Reference('event_dispatcher')]);
            $definition->addMethodCall('setLogger', [new Reference('logger')]);
            $definition->addMethodCall('setCollectionName', [$collectionName]);
        }
    }
    /**
     * Process crud tags.
     *
     * @param ContainerBuilder $container
     */
    protected function processCrudTag(ContainerBuilder $container)
    {
        $defaultClass = 'Velocity\\Bundle\\ApiBundle\\Service\\Base\\DocumentService';

        foreach ($container->findTaggedServiceIds('api.crud') as $id => $attributes) {
            $typeName = substr($id, strrpos($id, '.') + 1);
            $definition = $container->getDefinition($id);
            if (!$definition->getClass()) {
                $definition->setClass($defaultClass);
            }
            $tagAttribute = array_shift($attributes);
            $type = isset($tagAttribute['type']) ? $tagAttribute['type'] : $typeName;
            $repositoryId = sprintf('app.repository.%s', $type);
            if (!$container->has($repositoryId)) {
                $this->createRepositoryDefinition($container, $repositoryId);
            }
            $definition->addMethodCall('setType', [$type]);
            $definition->addMethodCall('setRepository', [new Reference($repositoryId)]);
            $definition->addMethodCall('setFormService', [new Reference('api.form')]);
            $definition->addMethodCall('setMetaDataService', [new Reference('api.metadata')]);
            $definition->addMethodCall('setLogger', [new Reference('logger')]);
            $definition->addMethodCall('setEventDispatcher', [new Reference('event_dispatcher')]);

            $rClass = new ReflectionClass($definition->getClass());
            $reader = new AnnotationReader();
            $metaDataServiceDefinition = $container->getDefinition('api.metadata');

            foreach($rClass->getMethods(\ReflectionMethod::IS_PUBLIC) as $rMethod) {
                foreach ($reader->getMethodAnnotations($rMethod) as $annotation) {
                    switch (true) {
                        case $annotation instanceof Callback:
                            $metaDataServiceDefinition->addMethodCall('addCallback', ['.' === $annotation->value{0} ? ($type . $annotation->value) : $annotation->value, [new Reference($id), $rMethod->getName()]]);
                            break;
                    }
                }
            }

        }
    }
    /**
     * Create and register a new repository definition.
     *
     * @param ContainerBuilder $container
     * @param $repositoryId
     *
     * @return Definition
     */
    protected function createRepositoryDefinition(ContainerBuilder $container, $repositoryId)
    {
        $defaultRepositoryClass = 'Velocity\\Bundle\\ApiBundle\\Service\\RepositoryService';

        $definition = new Definition($defaultRepositoryClass);

        $container->setDefinition($repositoryId, $definition);

        return $definition;
    }
    /**
     * Process sub crud tags.
     *
     * @param ContainerBuilder $container
     */
    protected function processSubCrudTag(ContainerBuilder $container)
    {
        $defaultClass = 'Velocity\\Bundle\\ApiBundle\\Service\\Base\\SubDocumentService';

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
            $definition->addMethodCall('setLogger', [new Reference('logger')]);
            $definition->addMethodCall('setEventDispatcher', [new Reference('event_dispatcher')]);
        }
    }
    /**
     * Process volatile tags.
     *
     * @param ContainerBuilder $container
     */
    protected function processVolatileTag(ContainerBuilder $container)
    {
        $defaultClass = 'Velocity\\Bundle\\ApiBundle\\Service\\Base\\VolatileDocumentService';

        foreach ($container->findTaggedServiceIds('api.volatile') as $id => $attributes) {
            $typeName = substr($id, strrpos($id, '.') + 1);
            $definition = $container->getDefinition($id);
            if (!$definition->getClass()) {
                $definition->setClass($defaultClass);
            }
            $tagAttribute = array_shift($attributes);
            $type = isset($tagAttribute['type']) ? $tagAttribute['type'] : $typeName;
            $definition->addMethodCall('setType', [$type]);
            $definition->addMethodCall('setFormService', [new Reference('api.form')]);
            $definition->addMethodCall('setMetaDataService', [new Reference('api.metadata')]);
            $definition->addMethodCall('setLogger', [new Reference('logger')]);
            $definition->addMethodCall('setEventDispatcher', [new Reference('event_dispatcher')]);

            $rClass = new ReflectionClass($definition->getClass());
            $reader = new AnnotationReader();
            $metaDataServiceDefinition = $container->getDefinition('api.metadata');

            foreach($rClass->getMethods(\ReflectionMethod::IS_PUBLIC) as $rMethod) {
                foreach ($reader->getMethodAnnotations($rMethod) as $annotation) {
                    switch (true) {
                        case $annotation instanceof Callback:
                            $metaDataServiceDefinition->addMethodCall('addCallback', ['.' === $annotation->value{0} ? ($type . $annotation->value) : $annotation->value, [new Reference($id), $rMethod->getName()]]);
                            break;
                    }
                }
            }

        }
    }
    /**
     * Process provider account tags.
     *
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
     * Process provider client tags.
     *
     * @param ContainerBuilder $container
     */
    protected function processProviderClientTag(ContainerBuilder $container)
    {
        $authenticationProviderDefinition = $container->getDefinition('api.security.authentication.provider');
        $requestServiceDefinition         = $container->getDefinition('api.request');

        foreach ($container->findTaggedServiceIds('api.provider.client') as $id => $attributes) {
            $attribute = array_shift($attributes);
            if ((isset($attribute['method']) && 'get' !== $attribute['method']) || isset($attribute['format'])) {
                $ref = new Definition('Velocity\\Bundle\\ApiBundle\\Service\\DecoratedClientService', [new Reference($id), isset($attribute['method']) ? $attribute['method'] : 'get', isset($attribute['format']) ? $attribute['format'] : 'raw']);
                $refId = 'api.client_' . md5(uniqid());
                $container->setDefinition($refId, $ref);
            } else {
                $refId = $id;
            }
            $authenticationProviderDefinition->addMethodCall('setClientService', [new Reference($refId)]);
            $requestServiceDefinition->addMethodCall('setClientService', [new Reference($refId)]);
        }
    }
    /**
     * Process migrator tags.
     *
     * @param ContainerBuilder $container
     */
    protected function processMigratorTag(ContainerBuilder $container)
    {
        $containerAwareInterface    = 'Symfony\\Component\\DependencyInjection\\ContainerAwareInterface';
        $loggerAwareInterface       = 'Psr\\Log\\LoggerAwareInterface';
        $migrationServiceDefinition = $container->getDefinition('api.migration');

        foreach ($container->findTaggedServiceIds('api.migrator') as $id => $attributes) {
            $definition = $container->getDefinition($id);
            foreach($attributes as $tagAttribute) {
                $extension = $tagAttribute['extension'];
                $class = $definition->getClass();
                $rClass = new \ReflectionClass($class);
                if ($rClass->isSubclassOf($containerAwareInterface)) {
                    $definition->addMethodCall('setContainer', [new Reference('service_container')]);
                }
                if ($rClass->isSubclassOf($loggerAwareInterface)) {
                    $definition->addMethodCall('setLogger', [new Reference('logger')]);
                }
                $migrationServiceDefinition->addMethodCall('addMigrator', [new Reference($id), $extension]);
            }
        }
    }
}