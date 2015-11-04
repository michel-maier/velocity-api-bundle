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
use Symfony\Component\HttpKernel\KernelInterface;
use Doctrine\Common\Annotations\AnnotationReader;
use Velocity\Core\Traits\ServiceTrait;
use Symfony\Component\DependencyInjection\Reference;
use Velocity\Bundle\ApiBundle\Annotation as Velocity;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Velocity\Bundle\ApiBundle\Service\Velocity\RepositoryIds;

/**
 * Velocity Service.
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
class VelocityService
{
    use ServiceTrait;
    /**
     * Tag Processors
     * @var array
     */
    protected $processors;
    
    /**
     * 
     * @var RepositoryIds
     */
    protected $repositoryIds;
    
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
            'codeGenerator.key'                   => 'velocity.codeGenerator',
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
            'storage.tag'             => 'velocity.storage',
        ];

        foreach ($defaults as $k => $v) {
            $this->setParameter('default_'.$k, $v);
        }
        
        $this->repositoryIds = new RepositoryIds();
        
        $tagProcessors = [
            'Repository',
            'Crud',
            'SubCrud',
            'SubSubCrud',
            'Volatile',
            'SubVolatile',
            'SubSubVolatile',
            'ProviderClient',
            'AccountProvider',
            'Migrator',
            'Action',
            'BusinessRule',
            'InvitationEvent',
            'Generator',
            'CodeGenerator',
            'Archiver',
            'Formatter',
            'Job',
            'Storage',
            'DocumentBuilder',
            'RepositoriesAware',
            'CrudsAware',
        ];
        
        foreach ($tagProcessors as $processor) {
            $this->addProcessor($processor);
        }
    }
    protected function addProcessor($processor)
    {
        $classname = 'Velocity\Bundle\ApiBundle\Service\Velocity\TagProcessor\\' . $processor . 'Processor';
        $this->processors[] = new $classname($this->repositoryIds, $this->getAnnotationReader());
    }
    /**
     * @param ContainerBuilder $container
     *
     * @return $this
     */
    public function analyzeTags(ContainerBuilder $container)
    {
        foreach ($this->processors as $processor) {
            $processor->process($container);
        }

        return $this;
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
                            /** @var Route $routeAnnotation */
                            $routeAnnotation = $this->getAnnotationReader()->getMethodAnnotation($rMethod, 'Sensio\\Bundle\\FrameworkExtraBundle\\Configuration\\Route');
                            if (null === $routeAnnotation) {
                                throw $this->createRequiredException('Sdk annotation require route annotation in controller');
                            }
                            unset($vars['service'], $vars['method'], $vars['type'], $vars['params'], $vars['value']);
                            $m->addMethodCall('addSdkMethod', [$class, $method, $routeAnnotation->getPath(), $a->service, $a->method, $a->type, $a->params, $vars]);
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
                        case $a instanceof Velocity\ReferenceList:
                            if (!$model) {
                                throw $this->createRequiredException('ReferenceList annotation only allowed in models');
                            }
                            $m->addMethodCall('addModelPropertyReferenceList', [$class, $property, $vars]);
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
                        case $a instanceof Velocity\Storage:
                            if (!$model) {
                                throw $this->createRequiredException('Storage annotation only allowed in models');
                            }
                            $m->addMethodCall('addModelPropertyStorage', [$class, $property, $vars]);
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
            switch ($storage['type']) {
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
