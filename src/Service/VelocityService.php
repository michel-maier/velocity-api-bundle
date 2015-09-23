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
            'action.key'                          => 'velocity.action',
            'documentBuilder.key'                 => 'velocity.documentBuilder',
            'event.key'                           => 'velocity.event',
            'storage.key'                         => 'velocity.storage',
            'formatter.key'                       => 'velocity.formatter',
            'businessRule.key'                    => 'velocity.businessRule',
            'invitationEvent.key'                 => 'velocity.invitationEvent',
            'generator.key'                       => 'velocity.generator',
            'archiver.key'                        => 'velocity.archiver',
            'job.key'                             => 'velocity.job',
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
            'database.key'                        => 'velocity.database',
            'redis.key'                           => 'redis',

            // container keys patterns
            'generated_client.key.pattern' => 'app.client_%s',

            // parameters keys
            'param.bundles.key'    => 'app_bundles',
            'param.events.key'     => 'app_events',
            'param.event_sets.key' => 'app_event_sets',
            'param.storages.key'   => 'app_storages',

            // parameters default values
            'param.bundles'    => [],
            'param.events'     => [],
            'param.event_sets' => [],
            'param.storages'   => [],

            // classes
            'repo.class'              => __NAMESPACE__.'\\RepositoryService',
            'crud.class'              => __NAMESPACE__.'\\Base\\DocumentService',
            'crud.sub.class'          => __NAMESPACE__.'\\Base\\SubDocumentService',
            'crud.sub.sub.class'      => __NAMESPACE__.'\\Base\\SubSubDocumentService',
            'volatile.class'          => __NAMESPACE__.'\\Base\\VolatileDocumentService',
            'volatile.sub.class'      => __NAMESPACE__.'\\Base\\VolatileSubDocumentService',
            'volatile.sub.sub.class'  => __NAMESPACE__.'\\Base\\VolatileSubSubDocumentService',
            'decorated_client.class'  => __NAMESPACE__.'\\DecoratedClientService',

            'storage.file.class'      => 'Velocity\\Bundle\\ApiBundle\\Storage\\FileStorage',
            'storage.redis.class'     => 'Velocity\\Bundle\\ApiBundle\\Storage\\RedisStorage',
            'storage.memory.class'    => 'Velocity\\Bundle\\ApiBundle\\Storage\\MemoryStorage',
            'storage.database.class'  => 'Velocity\\Bundle\\ApiBundle\\Storage\\DatabaseStorage',

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
            'action.tag'              => 'velocity.action',
            'business_rule.tag'       => 'velocity.business_rule',
            'invitation_event.tag'    => 'velocity.invitation_event',
            'generator.tag'           => 'velocity.generator',
            'repositories_aware.tag'  => 'velocity.repositories_aware',
            'archiver.tag'            => 'velocity.archiver',
            'job.tag'                 => 'velocity.job',
            'storage.tag'             => 'velocity.storage',
            'formatter.tag'           => 'velocity.formatter',
            'document_builder.tag'    => 'velocity.document_builder',

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
        $this->loadDynamicTags($container);
        $this->analyzeTags($container);
        $this->loadActionListeners($container);

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
        $trackedBundles = $container->hasParameter($this->getDefault('param.bundles.key'))
            ? $container->getParameter($this->getDefault('param.bundles.key'))
            : $this->getDefault('param.bundles');

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
     * @throws \Exception
     */
    public function loadClassesMetaData($classes, Definition $m)
    {
        foreach ($classes as $class) {
            $rClass = new \ReflectionClass($class);
            $model = false;
            foreach ($this->getAnnotationReader()->getClassAnnotations($rClass) as $a) {
                switch (true) {
                    case $a instanceof Velocity\Model:
                        $m->addMethodCall('addModel', [$class, []]);
                        $model = true;
                        break;
                }
            }
            foreach ($rClass->getMethods(\ReflectionMethod::IS_PUBLIC) as $rMethod) {
                foreach ($this->getAnnotationReader()->getMethodAnnotations($rMethod) as $a) {
                    $vars = get_object_vars($a);
                    $method = $rMethod->getName();
                    switch (true) {
                        case $a instanceof Velocity\Sdk:
                            unset($vars['service'], $vars['method'], $vars['type'], $vars['params'], $vars['value']);
                            $m->addMethodCall('addSdkMethod', [$class, $method, $a->service, $a->method, $a->type, $a->params, $vars]);
                            break;
                    }
                }
            }
            foreach ($rClass->getProperties(\ReflectionProperty::IS_PUBLIC) as $rProperty) {
                foreach ($this->getAnnotationReader()->getPropertyAnnotations($rProperty) as $a) {
                    $vars     = get_object_vars($a);
                    $property = $rProperty->getName();
                    switch (true) {
                        case $a instanceof Velocity\EmbeddedReference:
                            if (!$model) {
                                throw $this->createRequiredException('EmbeddedReference annotation only allowed in models');
                            }
                            $m->addMethodCall('addModelPropertyEmbeddedReference', [$class, $property, $vars]);
                            break;
                        case $a instanceof Velocity\EmbeddedReferenceList:
                            if (!$model) {
                                throw $this->createRequiredException('EmbeddedReferenceList annotation only allowed in models');
                            }
                            $m->addMethodCall('addModelPropertyEmbeddedReferenceList', [$class, $property, $vars]);
                            break;
                        case $a instanceof Velocity\Refresh:
                            if (!$model) {
                                throw $this->createRequiredException('Refresh annotation only allowed in models');
                            }
                            $m->addMethodCall('addModelPropertyRefresh', [$class, $property, $vars]);
                            break;
                        case $a instanceof Velocity\Enum:
                            if (!$model) {
                                throw $this->createRequiredException('Enum annotation only allowed in models');
                            }
                            $m->addMethodCall('addModelPropertyEnum', [$class, $property, $vars]);
                            break;
                        case $a instanceof Velocity\Generated:
                            if (!$model) {
                                throw $this->createRequiredException('Generated annotation only allowed in models');
                            }
                            $m->addMethodCall('addModelPropertyGenerated', [$class, $property, $vars]);
                            break;
                        case $a instanceof Velocity\Id:
                            if (!$model) {
                                throw $this->createRequiredException('Id annotation only allowed in models');
                            }
                            $m->addMethodCall('addModelPropertyId', [$class, $property, $vars]);
                            break;
                        case $a instanceof Type:
                            if (!$model) {
                                throw $this->createRequiredException('Type annotation only allowed in models (found in: %s)', $class);
                            }
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
        $this->processRepositoryTag($container);
        $this->processCrudTag($container);
        $this->processSubCrudTag($container);
        $this->processSubSubCrudTag($container);
        $this->processVolatileTag($container);
        $this->processSubVolatileTag($container);
        $this->processSubSubVolatileTag($container);
        $this->processProviderClientTag($container);
        $this->processProviderAccountTag($container);
        $this->processMigratorTag($container);
        $this->processActionTag($container);
        $this->processBusinessRuleTag($container);
        $this->processInvitationEventTag($container);
        $this->processGeneratorTag($container);
        $this->processArchiverTag($container);
        $this->processFormatterTag($container);
        $this->processJobTag($container);
        $this->processStorageTag($container);
        $this->processDocumentBuilderTag($container);
        $this->processRepositoriesAwareTag($container);

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
            $params = array_shift($attributes) + ['id' => $typeName];
            $d->addMethodCall('setCollectionName', [isset($params['collection']) ? $params['collection'] : $typeName]);
            $this->addLoggerSetterCall($d);
            $this->addDatabaseSetterCall($d);
            $this->addTranslatorSetterCall($d);
            $this->addEventDispatcherSetterCall($d);
            $this->setArrayParameterKey('repositoryIds', strtolower($params['id']), $id);
        }
    }
    /**
     * @param ContainerBuilder $container
     * @param string           $id
     * @param Definition       $d
     * @param array            $types
     *
     * @return $this
     */
    protected function populateModelService(ContainerBuilder $container, $id, Definition $d, array $types)
    {
        $m = $this->getMetaDataDefinitionFromContainer($container);

        $this->addFormSetterCall($d);
        $this->addMetaDataSetterCall($d);
        $this->addBusinessRuleSetterCall($d);
        $this->addLoggerSetterCall($d);
        $this->addEventDispatcherSetterCall($d);

        $rClass = new ReflectionClass($d->getClass());

        foreach ($rClass->getMethods(\ReflectionMethod::IS_PUBLIC) as $rMethod) {
            foreach ($this->getAnnotationReader()->getMethodAnnotations($rMethod) as $a) {
                $method = $rMethod->getName();
                switch (true) {
                    case $a instanceof Velocity\Callback:
                        $m->addMethodCall('addCallback', ['.' === $a->value{0} ? (join('.', $types).$a->value) : $a->value, [$this->ref($id), $method]]);
                        break;
                }
            }
        }

        return $this;
    }
    /**
     * Process crud tags.
     *
     * @param ContainerBuilder $container
     *
     * @throws \Exception
     */
    protected function processCrudTag(ContainerBuilder $container)
    {
        foreach ($this->findVelocityTaggedServiceIds($container, 'crud') as $id => $attributes) {
            list($type) = array_slice(explode('.', $id), -1);
            $d = $container->getDefinition($id);
            $this->ensureDefinitionClassSet($d, 'crud');
            $d->addMethodCall('setTypes', [[$type]]);
            $params = array_shift($attributes) + ['repo' => $type];
            $this->addRepositorySetterCall($d, $this->getRepositoryId($params['repo']));
            $this->populateModelService($container, $id, $d, [$type]);
        }
    }
    /**
     * @param string $alias
     * @return string
     *
     * @throws \Exception
     */
    protected function getRepositoryId($alias)
    {
        $alias = strtolower($alias);

        if (!$this->hasArrayParameterKey('repositoryIds', $alias)) {
            throw $this->createRequiredException("Unknown repository '%s'", $alias);
        }

        return $this->getArrayParameterKey('repositoryIds', $alias);
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
            $params = array_shift($attrs) + ['repo' => $type];
            $this->addRepositorySetterCall($d, $this->getRepositoryId($params['repo']));
            $this->populateModelService($container, $id, $d, [$type, $subType]);
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
            $params = array_shift($attrs) + ['repo' => $type];
            $this->addRepositorySetterCall($d, $this->getRepositoryId($params['repo']));
            $this->populateModelService($container, $id, $d, [$type, $subType, $subSubType]);
        }
    }
    /**
     * Process volatile tags.
     *
     * @param ContainerBuilder $container
     */
    protected function processVolatileTag(ContainerBuilder $container)
    {
        foreach ($this->findVelocityTaggedServiceIds($container, 'volatile') as $id => $attrs) {
            list($type) = array_slice(explode('.', $id), -1);
            $d = $container->getDefinition($id);
            $this->ensureDefinitionClassSet($d, 'volatile');
            $d->addMethodCall('setTypes', [[$type]]);
            $this->populateModelService($container, $id, $d, [$type]);
        }
    }
    /**
     * Process sub volatile tags.
     *
     * @param ContainerBuilder $container
     */
    protected function processSubVolatileTag(ContainerBuilder $container)
    {
        foreach ($this->findVelocityTaggedServiceIds($container, 'volatile.sub') as $id => $attrs) {
            list($type, $subType) = array_slice(explode('.', $id), -3);
            $d = $container->getDefinition($id);
            $this->ensureDefinitionClassSet($d, 'volatile.sub');
            $d->addMethodCall('setTypes', [[$type, $subType]]);
            $this->populateModelService($container, $id, $d, [$type, $subType]);
        }
    }
    /**
     * Process sub sub volatile tags.
     *
     * @param ContainerBuilder $container
     */
    protected function processSubSubVolatileTag(ContainerBuilder $container)
    {
        foreach ($this->findVelocityTaggedServiceIds($container, 'volatile.sub.sub') as $id => $attrs) {
            list($type, $subType, $subSubType) = array_slice(explode('.', $id), -3);
            $d = $container->getDefinition($id);
            $this->ensureDefinitionClassSet($d, 'volatile.sub.sub');
            $d->addMethodCall('setTypes', [[$type, $subType, $subSubType]]);
            $this->populateModelService($container, $id, $d, [$type, $subType, $subSubType]);
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
    protected function processActionTag(ContainerBuilder $container)
    {
        $actionDefinition = $container->getDefinition($this->getDefault('action.key'));

        foreach ($this->findVelocityTaggedServiceIds($container, 'action') as $id => $attributes) {
            $d = $container->getDefinition($id);
            foreach ($attributes as $params) {
                unset($params);
                $rClass = new \ReflectionClass($d->getClass());
                foreach ($rClass->getMethods(\ReflectionProperty::IS_PUBLIC) as $rMethod) {
                    foreach ($this->getAnnotationReader()->getMethodAnnotations($rMethod) as $a) {
                        $vars   = get_object_vars($a);
                        $method = $rMethod->getName();
                        switch (true) {
                            case $a instanceof Velocity\Action:
                                $name = $vars['value'];
                                unset($vars['value']);
                                $actionDefinition->addMethodCall('register', [$name, [$this->ref($id), $method], $vars]);
                                break;
                        }
                    }
                }
            }
        }
    }
    /**
     * Process event action tags.
     *
     * @param ContainerBuilder $container
     */
    protected function processBusinessRuleTag(ContainerBuilder $container)
    {
        $businessRuleDefinition = $container->getDefinition($this->getDefault('businessRule.key'));

        foreach ($this->findVelocityTaggedServiceIds($container, 'business_rule') as $id => $attributes) {
            $d = $container->getDefinition($id);
            foreach ($attributes as $params) {
                unset($params);
                $rClass = new \ReflectionClass($d->getClass());
                foreach ($rClass->getMethods(\ReflectionProperty::IS_PUBLIC) as $rMethod) {
                    foreach ($this->getAnnotationReader()->getMethodAnnotations($rMethod) as $a) {
                        $vars   = get_object_vars($a);
                        $method = $rMethod->getName();
                        switch (true) {
                            case $a instanceof Velocity\BusinessRule:
                                if (!isset($vars['id'])) {
                                    $vars['id'] = isset($vars['value']) ? $vars['value'] : null;
                                }
                                $brId = strtoupper($vars['id']);
                                $brName = strtolower(isset($vars['name']) ? $vars['name'] : trim(join(' ', preg_split('/(?=\\p{Lu})/', ucfirst($method)))));
                                unset($vars['value'], $vars['id'], $vars['name']);
                                $businessRuleDefinition->addMethodCall('register', [$brId, $brName, [$this->ref($id), $method], $vars]);
                                break;
                        }
                    }
                }
            }
        }
    }
    /**
     * Process invitation event tags.
     *
     * @param ContainerBuilder $container
     */
    protected function processInvitationEventTag(ContainerBuilder $container)
    {
        $invitationEventDefinition = $container->getDefinition($this->getDefault('invitationEvent.key'));

        foreach ($this->findVelocityTaggedServiceIds($container, 'invitation_event') as $id => $attributes) {
            $d = $container->getDefinition($id);
            foreach ($attributes as $params) {
                unset($params);
                $rClass = new \ReflectionClass($d->getClass());
                foreach ($rClass->getMethods(\ReflectionProperty::IS_PUBLIC) as $rMethod) {
                    foreach ($this->getAnnotationReader()->getMethodAnnotations($rMethod) as $a) {
                        $vars   = get_object_vars($a);
                        $method = $rMethod->getName();
                        switch (true) {
                            case $a instanceof Velocity\InvitationEvent:
                                if (!isset($vars['type'])) {
                                    $vars['type'] = isset($vars['value']) ? $vars['value'] : null;
                                }
                                $ieType = $vars['type'];
                                $ieTransition = $vars['transition'];
                                unset($vars['value'], $vars['type'], $vars['transition']);
                                $invitationEventDefinition->addMethodCall('register', [$ieType, $ieTransition, [$this->ref($id), $method], $vars]);
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
    /**
     * Process document builder tags.
     *
     * @param ContainerBuilder $container
     */
    protected function processDocumentBuilderTag(ContainerBuilder $container)
    {
        $dbDefinition = $container->getDefinition($this->getDefault('documentBuilder.key'));

        foreach ($this->findVelocityTaggedServiceIds($container, 'document_builder') as $id => $attributes) {
            foreach ($attributes as $params) {
                $type = $params['type'];
                unset($params['type']);
                $dbDefinition->addMethodCall('register', [$type, [$this->ref($id), 'build'], $params]);
            }
        }
    }
    /**
     * Process archiver tags.
     *
     * @param ContainerBuilder $container
     */
    protected function processArchiverTag(ContainerBuilder $container)
    {
        $archiverDefinition = $container->getDefinition($this->getDefault('archiver.key'));

        foreach ($this->findVelocityTaggedServiceIds($container, 'archiver') as $id => $attributes) {
            $d = $container->getDefinition($id);
            foreach ($attributes as $params) {
                unset($params);
                $rClass = new \ReflectionClass($d->getClass());
                foreach ($rClass->getMethods(\ReflectionProperty::IS_PUBLIC) as $rMethod) {
                    foreach ($this->getAnnotationReader()->getMethodAnnotations($rMethod) as $a) {
                        $vars   = get_object_vars($a);
                        $method = $rMethod->getName();
                        switch (true) {
                            case $a instanceof Velocity\Archiver:
                                $type = $vars['value'];
                                unset($vars['value']);
                                $archiverDefinition->addMethodCall('register', [$type, [$this->ref($id), $method], $vars]);
                                break;
                        }
                    }
                }
            }
        }
    }
    /**
     * Process formatter tags.
     *
     * @param ContainerBuilder $container
     */
    protected function processFormatterTag(ContainerBuilder $container)
    {
        $formatterDefinition = $container->getDefinition($this->getDefault('formatter.key'));

        foreach ($this->findVelocityTaggedServiceIds($container, 'formatter') as $id => $attributes) {
            $d = $container->getDefinition($id);
            foreach ($attributes as $params) {
                unset($params);
                $rClass = new \ReflectionClass($d->getClass());
                foreach ($rClass->getMethods(\ReflectionProperty::IS_PUBLIC) as $rMethod) {
                    foreach ($this->getAnnotationReader()->getMethodAnnotations($rMethod) as $a) {
                        $vars   = get_object_vars($a);
                        $method = $rMethod->getName();
                        switch (true) {
                            case $a instanceof Velocity\Formatter:
                                $type = $vars['value'];
                                unset($vars['value']);
                                $formatterDefinition->addMethodCall('register', [$type, [$this->ref($id), $method], $vars]);
                                break;
                        }
                    }
                }
            }
        }
    }
    /**
     * Process job tags.
     *
     * @param ContainerBuilder $container
     */
    protected function processJobTag(ContainerBuilder $container)
    {
        $jobDefinition = $container->getDefinition($this->getDefault('job.key'));

        foreach ($this->findVelocityTaggedServiceIds($container, 'job') as $id => $attributes) {
            $d = $container->getDefinition($id);
            foreach ($attributes as $params) {
                unset($params);
                $rClass = new \ReflectionClass($d->getClass());
                foreach ($rClass->getMethods(\ReflectionProperty::IS_PUBLIC) as $rMethod) {
                    foreach ($this->getAnnotationReader()->getMethodAnnotations($rMethod) as $a) {
                        $vars   = get_object_vars($a);
                        $method = $rMethod->getName();
                        switch (true) {
                            case $a instanceof Velocity\Job:
                                $name = $vars['value'];
                                unset($vars['value']);
                                $jobDefinition->addMethodCall('register', [$name, [$this->ref($id), $method], $vars]);
                                break;
                        }
                    }
                }
            }
        }
    }
    /**
     * Process storage tags.
     *
     * @param ContainerBuilder $container
     */
    protected function processStorageTag(ContainerBuilder $container)
    {
        $storageDefinition = $container->getDefinition($this->getDefault('storage.key'));

        foreach ($this->findVelocityTaggedServiceIds($container, 'storage') as $id => $attributes) {
            foreach ($attributes as $params) {
                $params += ['name' => null, 'mount' => '/'];
                $storageDefinition->addMethodCall('mount', [$params['name'], $params['mount'], $this->ref($id)]);
            }
        }
    }
    /**
     * Process repositories aware tags.
     *
     * @param ContainerBuilder $container
     */
    protected function processRepositoriesAwareTag(ContainerBuilder $container)
    {
        foreach ($this->findVelocityTaggedServiceIds($container, 'repositories_aware') as $id => $attributes) {
            $d = $container->getDefinition($id);
            foreach ($attributes as $params) {
                $params += ['method' => 'addRepository'];
                foreach ($this->getArrayParameter('repositoryIds') as $repoAlias => $repoId) {
                    $d->addMethodCall($params['method'], [$repoAlias, new Reference($repoId)]);
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
    protected function addBusinessRuleSetterCall(Definition $definition)
    {
        $definition->addMethodCall('setBusinessRuleService', [$this->ref('businessRule')]);
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
     * @param ContainerBuilder $container
     *
     * @throws \Exception
     */
    protected function loadDynamicTags(ContainerBuilder $container)
    {
        foreach ($container->getParameter($this->getDefault('param.storages.key', $this->getDefault('param.storages'))) as $storageName => $storage) {
            $storage = (is_array($storage) ? ($storage) : []) + ['mount' => '/', 'type' => 'file'];
            switch($storage['type']) {
                case 'file':
                    $d = new Definition($this->getDefault('storage.file.class'), [$storage['root'], $this->ref('filesystem')]);
                    break;
                case 'memory':
                    $d = new Definition($this->getDefault('storage.memory.class'));
                    break;
                case 'redis':
                    $d = new Definition($this->getDefault('storage.redis.class'), [$this->ref('redis')]);
                    break;
                case 'database':
                    $d = new Definition($this->getDefault('storage.database.class'), [$storage['collection'], $this->ref('database')]);
                    break;
                default:
                    throw $this->createMalformedException("Unsupported storage type '%s'", $storage['type']);
            }
            $d->addTag($this->getDefault('storage.tag'), ['name' => $storageName, 'mount' => $storage['mount']]);
            $container->setDefinition(sprintf('velocity.generated.storages.%s', $storageName), $d);

        }
    }
    /**
     * @param ContainerBuilder $container
     */
    protected function loadActionListeners(ContainerBuilder $container)
    {
        $ea = $container->getDefinition($this->getServiceKey('event'));

        foreach ($container->getParameter($this->getDefault('param.event_sets.key', $this->getDefault('param.event_sets'))) as $setName => $actions) {
            $ea->addMethodCall('registerSet', [$setName, $actions]);
        }

        foreach ($container->getParameter($this->getDefault('param.events.key', $this->getDefault('param.events'))) as $eventName => $info) {
            $eventName = false === strpos($eventName, '.') ? str_replace('_', '.', $eventName) : $eventName;
            $ea->addTag('kernel.event_listener', ['event' => $eventName, 'method' => 'consume']);
            foreach ($info['actions'] as $action) {
                $ea->addMethodCall('register', [$eventName, $action['action'], $action['params']]);
            }
        }
    }
}
