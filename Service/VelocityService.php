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

use ReflectionClass;
use ReflectionProperty;
use JMS\Serializer\Annotation\Type;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\HttpKernel\KernelInterface;
use Doctrine\Common\Annotations\AnnotationReader;
use Velocity\Bundle\ApiBundle\Traits\ServiceTrait;
use Symfony\Component\DependencyInjection\Reference;
use Velocity\Bundle\ApiBundle\Annotation as Velocity;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Velocity Service.
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
class VelocityService
{
    use ServiceTrait;
    /**
     * @param AnnotationReader $reader
     * @param array            $defaults
     */
    public function __construct(AnnotationReader $reader, array $defaults = [])
    {
        $this->setAnnotationReader($reader);

        $defaults += [

            // container keys
            'metaData.key'                        => 'velocity.metaData',
            'eventAction.key'                     => 'velocity.eventAction',
            'generator.key'                       => 'velocity.generator',
            'db.key'                              => 'velocity.database',
            'form.key'                            => 'velocity.form',
            'request.key'                         => 'velocity.request',
            'migration.key'                       => 'velocity.migration',
            'user_provider.default.key'           => 'velocity.security.provider.user.api',
            'authentication_provider.default.key' => 'velocity.security.authentication.provider',
            'logger.key'                          => 'logger',
            'event_dispatcher.key'                => 'event_dispatcher',
            'translator.key'                      => 'translator',
            'service_container.key'               => 'service_container',

            // container keys patterns
            'repo.key.pattern'             => 'app.repository.%s',
            'generated_client.key.pattern' => 'app.client_%s',

            // parameters keys
            'param.modelsBundles.key' => 'app_models_bundles',
            'param.events.key'        => 'app_events',

            // parameters default values
            'param.modelsBundles'     => [],
            'param.events'            => [],

            // classes
            'repo.class'              => __NAMESPACE__.'\\RepositoryService',
            'crud.class'              => __NAMESPACE__.'\\Base\\DocumentService',
            'crud.sub.class'          => __NAMESPACE__.'\\Base\\SubDocumentService',
            'crud.sub.sub.class'      => __NAMESPACE__.'\\Base\\SubSubDocumentService',
            'volatile.class'          => __NAMESPACE__.'\\Base\\VolatileDocumentService',
            'volatile.sub.class'      => __NAMESPACE__.'\\Base\\VolatileSubDocumentService',
            'volatile.sub.sub.class'  => __NAMESPACE__.'\\Base\\VolatileSubSubDocumentService',
            'decorated_client.class'  => __NAMESPACE__.'\\DecoratedClientService',

            // interfaces
            'container_aware.interface' => 'Symfony\\Component\\DependencyInjection\\ContainerAwareInterface',
            'logger_aware.interface'    => 'Psr\\Log\\LoggerAwareInterface',

            // namespaces
            'annotation.namespace'    => 'Velocity\\Bundle\\ApiBundle\\Annotation',

            // tags
            'repo.tag'                => 'velocity.repository',
            'crud.tag'                => 'velocity.crud',
            'crud.sub.tag'            => 'velocity.crud.sub',
            'crud.sub.sub.tag'        => 'velocity.crud.sub.sub',
            'volatile.tag'            => 'velocity.volatile',
            'volatile.sub.tag'        => 'velocity.volatile.sub',
            'volatile.sub.sub.tag'    => 'velocity.volatile.sub.sub',
            'account_provider.tag'    => 'velocity.provider.account',
            'client_provider.tag'     => 'velocity.provider.client',
            'migrator.tag'            => 'velocity.migrator',
            'event_action.tag'        => 'velocity.event_action',
            'generator.tag'           => 'velocity.generator',

        ];

        foreach ($defaults as $k => $v) {
            $this->setParameter('default_'.$k, $v);
        }
    }
    /**
     * @param AnnotationReader $reader
     *
     * @return $this
     */
    public function setAnnotationReader(AnnotationReader $reader)
    {
        return $this->setService('annotationReader', $reader);
    }
    /**
     * @return AnnotationReader
     */
    public function getAnnotationReader()
    {
        return $this->getService('annotationReader');
    }
    /**
     * @param ContainerBuilder $container
     * @param KernelInterface  $kernel
     *
     * @return $this
     */
    public function load(ContainerBuilder $container, KernelInterface $kernel)
    {
        $this->analyzeClasses($container, $kernel);
        $this->analyzeTags($container);
        $this->loadEventActionListeners($container);

        return $this;
    }
    /**
     * @param ContainerBuilder $container
     * @param KernelInterface  $kernel
     *
     * @return $this
     */
    public function analyzeClasses(ContainerBuilder $container, KernelInterface $kernel)
    {
        $trackedBundles = $container->hasParameter($this->getDefault('param.modelsBundles.key'))
            ? $container->getParameter($this->getDefault('param.modelsBundles.key'))
            : $this->getDefault('param.modelsBundles');

        $classes = [];

        foreach ($trackedBundles as $trackedBundle) {
            $classes = array_merge(
                $classes,
                $this->findVelocityAnnotatedClassesInDirectory($kernel->getBundle($trackedBundle)->getPath())
            );
        }

        return $this->loadClassesMetaData(
            $classes,
            $this->getMetaDataDefinitionFromContainer($container)
        );
    }
    /**
     * @param array      $classes
     * @param Definition $m       MetaData service definition
     *
     * @return $this
     *
     */
    public function loadClassesMetaData($classes, Definition $m)
    {
        foreach ($classes as $class) {
            $rClass = new \ReflectionClass($class);
            foreach ($this->getAnnotationReader()->getClassAnnotations($rClass) as $a) {
                switch (true) {
                    case $a instanceof Velocity\Model:
                        $m->addMethodCall('addModel', [$class, []]);
                        break;
                }
            }
            foreach ($rClass->getProperties(\ReflectionProperty::IS_PUBLIC) as $rProperty) {
                foreach ($this->getAnnotationReader()->getPropertyAnnotations($rProperty) as $a) {
                    $vars     = get_object_vars($a);
                    $property = $rProperty->getName();
                    switch (true) {
                        case $a instanceof Velocity\EmbeddedReference:
                            $m->addMethodCall('addModelPropertyEmbeddedReference', [$class, $property, $vars]);
                            break;
                        case $a instanceof Velocity\EmbeddedReferenceList:
                            $m->addMethodCall('addModelPropertyEmbeddedReferenceList', [$class, $property, $vars]);
                            break;
                        case $a instanceof Velocity\Refresh:
                            $m->addMethodCall('addModelPropertyRefresh', [$class, $property, $vars]);
                            break;
                        case $a instanceof Velocity\Generated:
                            $m->addMethodCall('addModelPropertyGenerated', [$class, $property, $vars]);
                            break;
                        case $a instanceof Velocity\Id:
                            $m->addMethodCall('addModelPropertyId', [$class, $property, $vars]);
                            break;
                        case $a instanceof Type:
                            $m->addMethodCall('setModelPropertyType', [$class, $property, $vars]);
                            break;
                    }
                }
            }
        }

        return $this;
    }
    /**
     * @param string $directory
     *
     * @return array
     */
    public function findVelocityAnnotatedClassesInDirectory($directory)
    {
        $f = new Finder();

        $f
            ->files()
            ->in($directory)
            ->name('*.php')
            ->contains($this->getDefault('annotation.namespace'))
            ->notPath('Tests')
            ->contains('class')
        ;

        $classes = [];

        foreach ($f as $file) {
            $matches = null;
            $ns = null;
            /** @var SplFileInfo $file */
            $content = $file->getContents();
            if (0 < preg_match('/namespace\s+([^\s;]+)\s*;/', $content, $matches)) {
                $ns = $matches[1].'\\';
            }
            if (0 < preg_match_all('/^\s*class\s+([^\s\:]+)\s+/m', $content, $matches)) {
                require_once $file->getRealPath();
                foreach ($matches[1] as $class) {
                    $fullClass = $ns.$class;
                    if (!$this->isVelocityAnnotatedClass($fullClass)) {
                        continue;
                    }
                    $classes[$fullClass] = true;
                }
            }
        }

        $classes = array_keys($classes);
        sort($classes);

        return $classes;
    }
    /**
     * @param string $class
     *
     * @return bool
     */
    public function isVelocityAnnotatedClass($class)
    {
        $rClass = new ReflectionClass($class);

        foreach ($this->getAnnotationReader()->getClassAnnotations($rClass) as $a) {
            if ($a instanceof Velocity\AnnotationInterface) {
                return true;
            }
        }
        foreach ($rClass->getMethods() as $rMethod) {
            foreach ($this->getAnnotationReader()->getMethodAnnotations($rMethod) as $a) {
                if ($a instanceof Velocity\AnnotationInterface) {
                    return true;
                }
            }
        }
        foreach ($rClass->getProperties() as $rProperty) {
            foreach ($this->getAnnotationReader()->getPropertyAnnotations($rProperty) as $a) {
                if ($a instanceof Velocity\AnnotationInterface) {
                    return true;
                }
            }
        }

        return false;
    }
    /**
     * @param ContainerBuilder $container
     *
     * @return $this
     */
    public function analyzeTags(ContainerBuilder $container)
    {
        $this->processCrudTag($container);
        $this->processSubCrudTag($container);
        $this->processSubSubCrudTag($container);
        $this->processRepositoryTag($container);
        $this->processVolatileTag($container);
        $this->processSubVolatileTag($container);
        $this->processSubSubVolatileTag($container);
        $this->processProviderClientTag($container);
        $this->processProviderAccountTag($container);
        $this->processMigratorTag($container);
        $this->processEventActionTag($container);
        $this->processGeneratorTag($container);

        return $this;
    }
    /**
     * @param string $key
     * @param mixed  $defaultValue
     *
     * @return mixed
     */
    protected function getDefault($key, $defaultValue = null)
    {
        if (null !== $defaultValue) {
            return $this->getParameterIfExists('default_'.$key, $defaultValue);
        }

        return $this->getParameter('default_'.$key);
    }
    /**
     * @param ContainerBuilder $container
     *
     * @return Definition
     */
    protected function getMetaDataDefinitionFromContainer(ContainerBuilder $container)
    {
        return $container->getDefinition($this->getDefault('metaData.key'));
    }
    /**
     * Process repository tags.
     *
     * @param ContainerBuilder $container
     */
    protected function processRepositoryTag(ContainerBuilder $container)
    {
        foreach ($this->findVelocityTaggedServiceIds($container, 'repo') as $id => $attributes) {
            $typeName = substr($id, strrpos($id, '.') + 1);
            $d = $container->getDefinition($id);
            if (!$d->getClass()) {
                $d->setClass($this->getDefault('repo.class'));
            }
            $params = array_shift($attributes);
            $d->addMethodCall('setCollectionName', [isset($params['collection']) ? $params['collection'] : $typeName]);
            $this->addLoggerSetterCall($d);
            $this->addDatabaseSetterCall($d);
            $this->addTranslatorSetterCall($d);
            $this->addEventDispatcherSetterCall($d);
        }
    }
    /**
     * Process crud tags.
     *
     * @param ContainerBuilder $container
     */
    protected function processCrudTag(ContainerBuilder $container)
    {
        $m = $this->getMetaDataDefinitionFromContainer($container);

        foreach ($this->findVelocityTaggedServiceIds($container, 'crud') as $id => $attributes) {
            list($type) = array_slice(explode('.', $id), -1);
            $d = $container->getDefinition($id);
            $this->ensureDefinitionClassSet($d, 'crud');
            $repositoryId = $this->buildRepoId(array_shift($attributes), $type);
            if (!$container->has($repositoryId)) {
                $this->createRepositoryDefinition($container, $repositoryId);
            }
            $d->addMethodCall('setTypes', [[$type]]);
            $this->addRepositorySetterCall($d, $repositoryId);
            $this->addFormSetterCall($d);
            $this->addMetaDataSetterCall($d);
            $this->addLoggerSetterCall($d);
            $this->addEventDispatcherSetterCall($d);

            $rClass = new ReflectionClass($d->getClass());

            foreach ($rClass->getMethods(\ReflectionMethod::IS_PUBLIC) as $rMethod) {
                foreach ($this->getAnnotationReader()->getMethodAnnotations($rMethod) as $a) {
                    $method = $rMethod->getName();
                    switch (true) {
                        case $a instanceof Velocity\Callback:
                            $m->addMethodCall('addCallback', ['.' === $a->value{0} ? ($type.$a->value) : $a->value, [$this->ref($id), $method]]);
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
        $d = new Definition();
        $d->addTag($this->getDefault('repo.tag'));

        $container->setDefinition($repositoryId, $d);

        return $d;
    }
    /**
     * Process sub crud tags.
     *
     * @param ContainerBuilder $container
     */
    protected function processSubCrudTag(ContainerBuilder $container)
    {
        foreach ($this->findVelocityTaggedServiceIds($container, 'crud.sub') as $id => $attrs) {
            list($type, $subType) = array_slice(explode('.', $id), -2);
            $d = $container->getDefinition($id);
            $this->ensureDefinitionClassSet($d, 'crud.sub');
            $d->addMethodCall('setTypes', [[$type, $subType]]);
            $this->addRepositorySetterCall($d, $this->buildRepoId(array_shift($attrs), $type));
            $this->addFormSetterCall($d);
            $this->addMetaDataSetterCall($d);
            $this->addLoggerSetterCall($d);
            $this->addEventDispatcherSetterCall($d);
        }
    }
    /**
     * Process sub crud tags.
     *
     * @param ContainerBuilder $container
     */
    protected function processSubSubCrudTag(ContainerBuilder $container)
    {
        foreach ($this->findVelocityTaggedServiceIds($container, 'crud.sub.sub') as $id => $attrs) {
            list($type, $subType, $subSubType) = array_slice(explode('.', $id), -3);
            $d = $container->getDefinition($id);
            $this->ensureDefinitionClassSet($d, 'crud.sub.sub');
            $d->addMethodCall('setTypes', [[$type, $subType, $subSubType]]);
            $this->addRepositorySetterCall($d, $this->buildRepoId(array_shift($attrs), $type));
            $this->addFormSetterCall($d);
            $this->addMetaDataSetterCall($d);
            $this->addLoggerSetterCall($d);
            $this->addEventDispatcherSetterCall($d);
        }
    }
    /**
     * Process volatile tags.
     *
     * @param ContainerBuilder $container
     */
    protected function processVolatileTag(ContainerBuilder $container)
    {
        $m = $this->getMetaDataDefinitionFromContainer($container);

        foreach ($this->findVelocityTaggedServiceIds($container, 'volatile') as $id => $attrs) {
            list($type) = array_slice(explode('.', $id), -1);
            $d = $container->getDefinition($id);
            $this->ensureDefinitionClassSet($d, 'volatile');
            $d->addMethodCall('setTypes', [[$type]]);
            $this->addFormSetterCall($d);
            $this->addMetaDataSetterCall($d);
            $this->addLoggerSetterCall($d);
            $this->addEventDispatcherSetterCall($d);

            $rClass = new ReflectionClass($d->getClass());

            foreach ($rClass->getMethods(\ReflectionMethod::IS_PUBLIC) as $rMethod) {
                foreach ($this->getAnnotationReader()->getMethodAnnotations($rMethod) as $a) {
                    $method = $rMethod->getName();
                    switch (true) {
                        case $a instanceof Velocity\Callback:
                            $m->addMethodCall('addCallback', ['.' === $a->value{0} ? ($type.$a->value) : $a->value, [$this->ref($id), $method]]);
                            break;
                    }
                }
            }
        }
    }
    /**
     * Process sub volatile tags.
     *
     * @param ContainerBuilder $container
     */
    protected function processSubVolatileTag(ContainerBuilder $container)
    {
        $m = $this->getMetaDataDefinitionFromContainer($container);

        foreach ($this->findVelocityTaggedServiceIds($container, 'volatile.sub') as $id => $attrs) {
            list($type, $subType) = array_slice(explode('.', $id), -3);
            $d = $container->getDefinition($id);
            $this->ensureDefinitionClassSet($d, 'volatile.sub');
            $d->addMethodCall('setTypes', [[$type, $subType]]);
            $this->addFormSetterCall($d);
            $this->addMetaDataSetterCall($d);
            $this->addLoggerSetterCall($d);
            $this->addEventDispatcherSetterCall($d);

            $rClass = new ReflectionClass($d->getClass());

            foreach ($rClass->getMethods(\ReflectionMethod::IS_PUBLIC) as $rMethod) {
                foreach ($this->getAnnotationReader()->getMethodAnnotations($rMethod) as $a) {
                    $method = $rMethod->getName();
                    switch (true) {
                        case $a instanceof Velocity\Callback:
                            $m->addMethodCall('addCallback', ['.' === $a->value{0} ? ($type.'.'.$subType.$a->value) : $a->value, [$this->ref($id), $method]]);
                            break;
                    }
                }
            }
        }
    }
    /**
     * Process sub sub volatile tags.
     *
     * @param ContainerBuilder $container
     */
    protected function processSubSubVolatileTag(ContainerBuilder $container)
    {
        $m = $this->getMetaDataDefinitionFromContainer($container);

        foreach ($this->findVelocityTaggedServiceIds($container, 'volatile.sub.sub') as $id => $attrs) {
            list($type, $subType, $subSubType) = array_slice(explode('.', $id), -3);
            $d = $container->getDefinition($id);
            $this->ensureDefinitionClassSet($d, 'volatile.sub.sub');
            $d->addMethodCall('setTypes', [[$type, $subType, $subSubType]]);
            $this->addFormSetterCall($d);
            $this->addMetaDataSetterCall($d);
            $this->addLoggerSetterCall($d);
            $this->addEventDispatcherSetterCall($d);

            $rClass = new ReflectionClass($d->getClass());

            foreach ($rClass->getMethods(\ReflectionMethod::IS_PUBLIC) as $rMethod) {
                foreach ($this->getAnnotationReader()->getMethodAnnotations($rMethod) as $a) {
                    $method = $rMethod->getName();
                    switch (true) {
                        case $a instanceof Velocity\Callback:
                            $m->addMethodCall('addCallback', ['.' === $a->value{0} ? ($type.'.'.$subType.'.'.$subSubType.$a->value) : $a->value, [$this->ref($id), $method]]);
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
        $userProviderDefinition = $container->getDefinition($this->getDefault('user_provider.default.key'));

        foreach ($this->findVelocityTaggedServiceIds($container, 'account_provider') as $id => $attrs) {
            foreach ($attrs as $params) {
                $type   = isset($params['type']) ? $params['type'] : 'default';
                $method = isset($params['method']) ? $params['method'] : 'get';
                $format = isset($params['format']) ? $params['format'] : 'plain';
                $userProviderDefinition->addMethodCall('setAccountProvider', [$this->ref($id), $type, $method, $format]);
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
        $authenticationProviderDefinition = $container->getDefinition($this->getDefault(('authentication_provider.default.key')));
        $requestServiceDefinition         = $container->getDefinition($this->getDefault('request.key'));

        foreach ($this->findVelocityTaggedServiceIds($container, 'client_provider') as $id => $attrs) {
            $attribute = array_shift($attrs);
            $refId = $id;
            if ((isset($attribute['method']) && 'get' !== $attribute['method']) || isset($attribute['format'])) {
                $ref = new Definition($this->getDefault('decorated_client.class'), [$this->ref($id), isset($attribute['method']) ? $attribute['method'] : 'get', isset($attribute['format']) ? $attribute['format'] : 'raw']);
                $refId = sprintf($this->getDefault('generated_client.key.pattern'), md5(uniqid()));
                $container->setDefinition($refId, $ref);
            }
            $authenticationProviderDefinition->addMethodCall('setClientProvider', [$this->ref($refId)]);
            $requestServiceDefinition->addMethodCall('setClientProvider', [$this->ref($refId)]);
        }
    }
    /**
     * Process migrator tags.
     *
     * @param ContainerBuilder $container
     */
    protected function processMigratorTag(ContainerBuilder $container)
    {
        $containerAwareInterface    = $this->getDefault('container_aware.interface');
        $loggerAwareInterface       = $this->getDefault('logger_aware.interface');
        $migrationServiceDefinition = $container->getDefinition($this->getDefault('migration.key'));

        foreach ($this->findVelocityTaggedServiceIds($container, 'migrator') as $id => $attributes) {
            $d = $container->getDefinition($id);
            foreach ($attributes as $params) {
                $extension = $params['extension'];
                $rClass = new \ReflectionClass($d->getClass());
                if ($rClass->isSubclassOf($containerAwareInterface)) {
                    $this->addContainerSetterCall($d);
                }
                if ($rClass->isSubclassOf($loggerAwareInterface)) {
                    $this->addLoggerSetterCall($d);
                }
                $migrationServiceDefinition->addMethodCall('addMigrator', [$this->ref($id), $extension]);
            }
        }
    }
    /**
     * Process event action tags.
     *
     * @param ContainerBuilder $container
     */
    protected function processEventActionTag(ContainerBuilder $container)
    {
        $eventActionDefinition = $container->getDefinition($this->getDefault('eventAction.key'));

        foreach ($this->findVelocityTaggedServiceIds($container, 'event_action') as $id => $attributes) {
            $d = $container->getDefinition($id);
            foreach ($attributes as $params) {
                unset($params);
                $rClass = new \ReflectionClass($d->getClass());
                foreach ($rClass->getMethods(\ReflectionProperty::IS_PUBLIC) as $rMethod) {
                    foreach ($this->getAnnotationReader()->getMethodAnnotations($rMethod) as $a) {
                        $vars   = get_object_vars($a);
                        $method = $rMethod->getName();
                        switch (true) {
                            case $a instanceof Velocity\EventAction:
                                $name = $vars['value'];
                                unset($vars['value']);
                                $eventActionDefinition->addMethodCall('register', [$name, [$this->ref($id), $method], $vars]);
                                break;
                        }
                    }
                }
            }
        }
    }
    /**
     * Process generator tags.
     *
     * @param ContainerBuilder $container
     */
    protected function processGeneratorTag(ContainerBuilder $container)
    {
        $generatorDefinition = $container->getDefinition($this->getDefault('generator.key'));

        foreach ($this->findVelocityTaggedServiceIds($container, 'generator') as $id => $attributes) {
            $d = $container->getDefinition($id);
            foreach ($attributes as $params) {
                unset($params);
                $rClass = new \ReflectionClass($d->getClass());
                foreach ($rClass->getMethods(\ReflectionProperty::IS_PUBLIC) as $rMethod) {
                    foreach ($this->getAnnotationReader()->getMethodAnnotations($rMethod) as $a) {
                        $vars   = get_object_vars($a);
                        $method = $rMethod->getName();
                        switch (true) {
                            case $a instanceof Velocity\Generator:
                                $name = $vars['value'];
                                unset($vars['value']);
                                $generatorDefinition->addMethodCall('register', [$name, [$this->ref($id), $method], $vars]);
                                break;
                        }
                    }
                }
            }
        }
    }
    protected function addRepositorySetterCall(Definition $definition, $repoId)
    {
        $definition->addMethodCall('setRepository', [$this->ref($repoId)]);
    }
    protected function addFormSetterCall(Definition $definition)
    {
        $definition->addMethodCall('setFormService', [$this->ref('form')]);
    }
    protected function addMetaDataSetterCall(Definition $definition)
    {
        $definition->addMethodCall('setMetaDataService', [$this->ref('metaData')]);
    }
    protected function addLoggerSetterCall(Definition $definition)
    {
        $definition->addMethodCall('setLogger', [$this->ref('logger')]);
    }
    protected function addEventDispatcherSetterCall(Definition $definition)
    {
        $definition->addMethodCall('setEventDispatcher', [$this->ref('event_dispatcher')]);
    }
    protected function addDatabaseSetterCall(Definition $definition)
    {
        $definition->addMethodCall('setDatabaseService', [$this->ref('db')]);
    }
    protected function addTranslatorSetterCall(Definition $definition)
    {
        $definition->addMethodCall('setTranslator', [$this->ref('translator')]);
    }
    protected function addContainerSetterCall(Definition $definition)
    {
        $definition->addMethodCall('setContainer', [$this->ref('service_container')]);
    }
    /**
     * @param Definition $definition
     * @param string     $defaultClassType
     */
    protected function ensureDefinitionClassSet(Definition $definition, $defaultClassType)
    {
        if ($definition->getClass()) {
            return;
        }

        $definition->setClass($this->getDefault($defaultClassType.'.class'));
    }
    /**
     * @param ContainerBuilder $container
     * @param string           $tagAlias
     *
     * @return array
     */
    protected function findVelocityTaggedServiceIds(ContainerBuilder $container, $tagAlias)
    {
        return $container->findTaggedServiceIds($this->getDefault($tagAlias.'.tag'));
    }
    /**
     * @param string $alias
     *
     * @return string
     */
    protected function getServiceKey($alias)
    {
        return $this->getDefault($alias.'.key', $alias);
    }
    /**
     * @param $alias
     *
     * @return Reference
     */
    protected function ref($alias)
    {
        return new Reference($this->getServiceKey($alias));
    }
    /**
     * @param $params
     * @param $typeName
     *
     * @return string
     */
    protected function buildRepoId($params, $typeName)
    {
        return isset($params['repo']) ? $params['repo'] : (sprintf($this->getDefault('repo.key.pattern'), $typeName));
    }
    /**
     * @param ContainerBuilder $container
     */
    protected function loadEventActionListeners(ContainerBuilder $container)
    {
        $ea = $container->getDefinition($this->getServiceKey('eventAction'));

        foreach ($container->getParameter($this->getDefault('param.events.key', $this->getDefault('param.events'))) as $eventName => $info) {
            $eventName = false === strpos($eventName, '.') ? str_replace('_', '.', $eventName) : $eventName;
            $ea->addTag('kernel.event_listener', ['event' => $eventName, 'method' => 'consume']);
            foreach ($info['actions'] as $action) {
                $ea->addMethodCall('addEventAction', [$eventName, $action['action'], $action['params']]);
            }
        }
    }
}
