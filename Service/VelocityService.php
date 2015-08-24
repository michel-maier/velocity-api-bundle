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
use Doctrine\Common\Annotations\AnnotationReader;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\HttpKernel\KernelInterface;
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
            'db.key'                              => 'velocity.database',
            'form.key'                            => 'velocity.form',
            'request.key'                         => 'velocity.request',
            'migration.key'                       => 'velocity.migration',
            'user_provider.default.key'           => 'velocity.security.provider.user.api',
            'authentication_provider.default.key' => 'velocity.security.authentication.provider',

            // container keys patterns
            'repo.key.pattern'             => 'app.repository.%s',
            'generated_client.key.pattern' => 'app.client_%s',

            // parameters keys
            'param.modelsBundles.key' => 'app_models_bundles',

            // parameters default values
            'param.modelsBundles'     => [],

            // classes
            'repo.class'              => __NAMESPACE__ . '\\RepositoryService',
            'crud.class'              => __NAMESPACE__ . '\\Base\\DocumentService',
            'crud.sub.class'          => __NAMESPACE__ . '\\Base\\SubDocumentService',
            'crud.sub.sub.class'      => __NAMESPACE__ . '\\Base\\SubSubDocumentService',
            'volatile.class'          => __NAMESPACE__ . '\\Base\\VolatileDocumentService',
            'volatile.sub.class'      => __NAMESPACE__ . '\\Base\\VolatileSubDocumentService',
            'volatile.sub.sub.class'  => __NAMESPACE__ . '\\Base\\VolatileSubSubDocumentService',
            'decorated_client.class'  => __NAMESPACE__ . '\\DecoratedClientService',

            // interfaces
            'container_aware.interface' => 'Symfony\\Component\\DependencyInjection\\ContainerAwareInterface',
            'logger_aware.interface'    => 'Psr\\Log\\LoggerAwareInterface',

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

        ];

        foreach($defaults as $k => $v) {
            $this->setParameter('default_' . $k, $v);
        }
    }
    /**
     * @param $key
     * @return mixed
     */
    protected function getDefault($key)
    {
        return $this->getParameter('default_' . $key);
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
     * @param KernelInterface $kernel
     *
     * @return $this
     */
    public function load(ContainerBuilder $container, KernelInterface $kernel)
    {
        $this->analyzeClasses($container, $kernel);
        $this->analyzeTags($container);

        return $this;
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

        foreach($trackedBundles as $trackedBundle) {
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
     * @param Definition $m MetaData service definition
     *
     * @return $this
     *
     */
    public function loadClassesMetaData($classes, Definition $m)
    {
        foreach($classes as $class) {
            $rClass = new \ReflectionClass($class);
            foreach($this->getAnnotationReader()->getClassAnnotations($rClass) as $a) {
                switch(true) {
                    case $a instanceof Velocity\Model:
                        $m->addMethodCall('addModel', [$class, []]);
                        break;
                }
            }
            foreach($rClass->getProperties(\ReflectionProperty::IS_PUBLIC) as $rProperty) {
                foreach($this->getAnnotationReader()->getPropertyAnnotations($rProperty) as $a) {
                    $vars     = get_object_vars($a);
                    $property = $rProperty->getName();
                    switch(true) {
                        case $a instanceof Velocity\EmbeddedReference:
                            $m->addMethodCall('addClassPropertyEmbeddedReference', [$class, $property, $vars]);
                            break;
                        case $a instanceof Velocity\EmbeddedReferenceList:
                            $m->addMethodCall('addClassPropertyEmbeddedReferenceList', [$class, $property, $vars]);
                            break;
                        case $a instanceof Velocity\Refresh:
                            $m->addMethodCall('addClassPropertyRefresh', [$class, $property, $vars]);
                            break;
                        case $a instanceof Velocity\Generated:
                            $m->addMethodCall('addClassPropertyGenerated', [$class, $property, $vars]);
                            break;
                        case $a instanceof Velocity\Id:
                            $m->addMethodCall('addClassPropertyId', [$class, $property, $vars]);
                            break;
                        case $a instanceof Type:
                            $m->addMethodCall('setClassPropertyType', [$class, $property, $vars]);
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

        $classes = [];

        foreach($f->files()->in($directory)->name('*.php')->contains('Velocity\\Bundle\\ApiBundle\\Annotation')->notPath('Tests')->contains('class') as $file) {
            $matches = null;
            $ns = null;
            /** @var SplFileInfo $file */
            $content = $file->getContents();
            if (0 < preg_match('/namespace\s+([^\s;]+)\s*;/', $content, $matches)) {
                $ns = $matches[1] . '\\';
            }
            if (0 < preg_match_all('/^\s*class\s+([^\s\:]+)\s+/m', $content, $matches)) {
                require_once $file->getRealPath();
                foreach($matches[1] as $class) {
                    $fullClass = $ns . $class;
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

        foreach($this->getAnnotationReader()->getClassAnnotations($rClass) as $a) {
            if ($a instanceof Velocity\AnnotationInterface) return true;
        }
        foreach($rClass->getMethods() as $rMethod) {
            foreach($this->getAnnotationReader()->getMethodAnnotations($rMethod) as $a) {
                if ($a instanceof Velocity\AnnotationInterface) return true;
            }
        }
        foreach($rClass->getProperties() as $rProperty) {
            foreach($this->getAnnotationReader()->getPropertyAnnotations($rProperty) as $a) {
                if ($a instanceof Velocity\AnnotationInterface) return true;
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

        return $this;
    }
    /**
     * Process repository tags.
     *
     * @param ContainerBuilder $container
     */
    protected function processRepositoryTag(ContainerBuilder $container)
    {
        foreach ($container->findTaggedServiceIds($this->getDefault('repo.tag')) as $id => $attributes) {
            $typeName = substr($id, strrpos($id, '.') + 1);
            $d = $container->getDefinition($id);
            if (!$d->getClass()) $d->setClass($this->getDefault('repo.class'));
            $tagAttribute = array_shift($attributes);
            $collectionName = isset($tagAttribute['collection']) ? $tagAttribute['collection'] : $typeName;
            $d->addMethodCall('setDatabaseService', [new Reference($this->getDefault('db.key'))]);
            $d->addMethodCall('setTranslator', [new Reference('translator')]);
            $d->addMethodCall('setEventDispatcher', [new Reference('event_dispatcher')]);
            $d->addMethodCall('setLogger', [new Reference('logger')]);
            $d->addMethodCall('setCollectionName', [$collectionName]);
        }
    }
    /**
     * Process crud tags.
     *
     * @param ContainerBuilder $container
     */
    protected function processCrudTag(ContainerBuilder $container)
    {
        foreach ($container->findTaggedServiceIds($this->getDefault('crud.tag')) as $id => $attributes) {
            $typeName = substr($id, strrpos($id, '.') + 1);
            $d = $container->getDefinition($id);
            if (!$d->getClass()) $d->setClass($this->getDefault('crud.class'));
            $tagAttribute = array_shift($attributes);
            $type = isset($tagAttribute['type']) ? $tagAttribute['type'] : $typeName;
            $repositoryId = sprintf($this->getDefault('repo.key.pattern'), $type);
            if (!$container->has($repositoryId)) {
                $this->createRepositoryDefinition($container, $repositoryId);
            }
            $d->addMethodCall('setType', [$type]);
            $d->addMethodCall('setRepository', [new Reference($repositoryId)]);
            $d->addMethodCall('setFormService', [new Reference($this->getDefault('form.key'))]);
            $d->addMethodCall('setMetaDataService', [new Reference($this->getDefault('metaData.key'))]);
            $d->addMethodCall('setLogger', [new Reference('logger')]);
            $d->addMethodCall('setEventDispatcher', [new Reference('event_dispatcher')]);

            $rClass = new ReflectionClass($d->getClass());
            $m = $this->getMetaDataDefinitionFromContainer($container);

            foreach($rClass->getMethods(\ReflectionMethod::IS_PUBLIC) as $rMethod) {
                foreach ($this->getAnnotationReader()->getMethodAnnotations($rMethod) as $a) {
                    $method = $rMethod->getName();
                    switch (true) {
                        case $a instanceof Velocity\Callback:
                            $m->addMethodCall('addCallback', ['.' === $a->value{0} ? ($type . $a->value) : $a->value, [new Reference($id), $method]]);
                            break;
                        case $a instanceof Velocity\Callback\AfterSave:
                            $m->addMethodCall('addCallback', ['.' === $a->value{0} ? ($type . $a->value) : $a->value, [new Reference($id), $method]]);
                            break;
                        case $a instanceof Velocity\Callback\BeforeCreateSave:
                            $m->addMethodCall('addCallback', ['.' === $a->value{0} ? ($type . $a->value) : $a->value, [new Reference($id), $method]]);
                            break;
                        case $a instanceof Velocity\Callback\BeforeDelete:
                            $m->addMethodCall('addCallback', ['.' === $a->value{0} ? ($type . $a->value) : $a->value, [new Reference($id), $method]]);
                            break;
                        case $a instanceof Velocity\Callback\BeforeSave:
                            $m->addMethodCall('addCallback', ['.' === $a->value{0} ? ($type . $a->value) : $a->value, [new Reference($id), $method]]);
                            break;
                        case $a instanceof Velocity\Callback\Created:
                            $m->addMethodCall('addCallback', ['.' === $a->value{0} ? ($type . $a->value) : $a->value, [new Reference($id), $method]]);
                            break;
                        case $a instanceof Velocity\Callback\Deleted:
                            $m->addMethodCall('addCallback', ['.' === $a->value{0} ? ($type . $a->value) : $a->value, [new Reference($id), $method]]);
                            break;
                        case $a instanceof Velocity\Callback\Saved:
                            $m->addMethodCall('addCallback', ['.' === $a->value{0} ? ($type . $a->value) : $a->value, [new Reference($id), $method]]);
                            break;
                        case $a instanceof Velocity\Callback\Updated:
                            $m->addMethodCall('addCallback', ['.' === $a->value{0} ? ($type . $a->value) : $a->value, [new Reference($id), $method]]);
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
        $definition = new Definition();
        $definition->addTag($this->getDefault('repo.tag'));

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
        foreach ($container->findTaggedServiceIds($this->getDefault('crud.sub.tag')) as $id => $attributes) {
            $tokens = explode('.', $id);
            $subTypeName = array_pop($tokens);
            $typeName = array_pop($tokens);
            $definition = $container->getDefinition($id);
            if (!$definition->getClass()) $definition->setClass($this->getDefault('crud.sub.class'));
            $tagAttribute = array_shift($attributes);
            $repositoryId = isset($tagAttribute['repo']) ? $tagAttribute['repo'] : (sprintf($this->getDefault('repo.key.pattern'), $typeName));
            $definition->addMethodCall('setType', [$typeName]);
            $definition->addMethodCall('setSubType', [$subTypeName]);
            $definition->addMethodCall('setRepository', [new Reference($repositoryId)]);
            $definition->addMethodCall('setFormService', [new Reference($this->getDefault('form.key'))]);
            $definition->addMethodCall('setMetaDataService', [new Reference($this->getDefault('metaData.key'))]);
            $definition->addMethodCall('setLogger', [new Reference('logger')]);
            $definition->addMethodCall('setEventDispatcher', [new Reference('event_dispatcher')]);
        }
    }
    /**
     * Process sub crud tags.
     *
     * @param ContainerBuilder $container
     */
    protected function processSubSubCrudTag(ContainerBuilder $container)
    {
        foreach ($container->findTaggedServiceIds($this->getDefault('crud.sub.sub.tag')) as $id => $attributes) {
            $tokens = explode('.', $id);
            $subSubTypeName = array_pop($tokens);
            $subTypeName = array_pop($tokens);
            $typeName = array_pop($tokens);
            $definition = $container->getDefinition($id);
            if (!$definition->getClass()) $definition->setClass($this->getDefault('crud.sub.sub.class'));
            $tagAttribute = array_shift($attributes);
            $repositoryId = isset($tagAttribute['repo']) ? $tagAttribute['repo'] : (sprintf($this->getDefault('repo.key.pattern'), $typeName));
            $definition->addMethodCall('setType', [$typeName]);
            $definition->addMethodCall('setSubType', [$subTypeName]);
            $definition->addMethodCall('setSubSubType', [$subSubTypeName]);
            $definition->addMethodCall('setRepository', [new Reference($repositoryId)]);
            $definition->addMethodCall('setFormService', [new Reference($this->getDefault('form.key'))]);
            $definition->addMethodCall('setMetaDataService', [new Reference($this->getDefault('metaData.key'))]);
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
        foreach ($container->findTaggedServiceIds($this->getDefault('volatile.tag')) as $id => $attributes) {
            $typeName = substr($id, strrpos($id, '.') + 1);
            $definition = $container->getDefinition($id);
            if (!$definition->getClass()) $definition->setClass($this->getDefault('volatile.tag'));
            $tagAttribute = array_shift($attributes);
            $type = isset($tagAttribute['type']) ? $tagAttribute['type'] : $typeName;
            $definition->addMethodCall('setType', [$type]);
            $definition->addMethodCall('setFormService', [new Reference($this->getDefault('form.key'))]);
            $definition->addMethodCall('setMetaDataService', [new Reference($this->getDefault('metaData.key'))]);
            $definition->addMethodCall('setLogger', [new Reference('logger')]);
            $definition->addMethodCall('setEventDispatcher', [new Reference('event_dispatcher')]);

            $rClass = new ReflectionClass($definition->getClass());
            $m = $this->getMetaDataDefinitionFromContainer($container);

            foreach($rClass->getMethods(\ReflectionMethod::IS_PUBLIC) as $rMethod) {
                foreach ($this->getAnnotationReader()->getMethodAnnotations($rMethod) as $a) {
                    $method = $rMethod->getName();
                    switch (true) {
                        case $a instanceof Velocity\Callback:
                            $m->addMethodCall('addCallback', ['.' === $a->value{0} ? ($type . $a->value) : $a->value, [new Reference($id), $method]]);
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
        foreach ($container->findTaggedServiceIds($this->getDefault('volatile.sub.tag')) as $id => $attributes) {
            $tokens = explode('.', $id);
            $subTypeName = array_pop($tokens);
            $typeName = array_pop($tokens);
            $definition = $container->getDefinition($id);
            if (!$definition->getClass()) $definition->setClass($this->getDefault('volatile.sub.class'));
            $tagAttribute = array_shift($attributes);
            $type = isset($tagAttribute['type']) ? $tagAttribute['type'] : $typeName;
            $subType = isset($tagAttribute['subType']) ? $tagAttribute['subType'] : $subTypeName;
            $definition->addMethodCall('setType', [$type]);
            $definition->addMethodCall('setSubType', [$subType]);
            $definition->addMethodCall('setFormService', [new Reference($this->getDefault('form.key'))]);
            $definition->addMethodCall('setMetaDataService', [new Reference($this->getDefault('metaData.key'))]);
            $definition->addMethodCall('setLogger', [new Reference('logger')]);
            $definition->addMethodCall('setEventDispatcher', [new Reference('event_dispatcher')]);

            $rClass = new ReflectionClass($definition->getClass());
            $m = $this->getMetaDataDefinitionFromContainer($container);

            foreach($rClass->getMethods(\ReflectionMethod::IS_PUBLIC) as $rMethod) {
                foreach ($this->getAnnotationReader()->getMethodAnnotations($rMethod) as $a) {
                    $method = $rMethod->getName();
                    switch (true) {
                        case $a instanceof Velocity\Callback:
                            $m->addMethodCall('addCallback', ['.' === $a->value{0} ? ($type . '.' . $subType . $a->value) : $a->value, [new Reference($id), $method]]);
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
        foreach ($container->findTaggedServiceIds($this->getDefault('volatile.sub.sub.tag')) as $id => $attributes) {
            $tokens = explode('.', $id);
            $subSubTypeName = array_pop($tokens);
            $subTypeName = array_pop($tokens);
            $typeName = array_pop($tokens);
            $definition = $container->getDefinition($id);
            if (!$definition->getClass()) $definition->setClass($this->getDefault('volatile.sub.sub.class'));
            $tagAttribute = array_shift($attributes);
            $type = isset($tagAttribute['type']) ? $tagAttribute['type'] : $typeName;
            $subType = isset($tagAttribute['subType']) ? $tagAttribute['subType'] : $subTypeName;
            $subSubType = isset($tagAttribute['subSubType']) ? $tagAttribute['subSubType'] : $subSubTypeName;
            $definition->addMethodCall('setType', [$type]);
            $definition->addMethodCall('setSubType', [$subType]);
            $definition->addMethodCall('setSubSubType', [$subSubType]);
            $definition->addMethodCall('setFormService', [new Reference($this->getDefault('form.key'))]);
            $definition->addMethodCall('setMetaDataService', [new Reference($this->getDefault('metaData.key'))]);
            $definition->addMethodCall('setLogger', [new Reference('logger')]);
            $definition->addMethodCall('setEventDispatcher', [new Reference('event_dispatcher')]);

            $rClass = new ReflectionClass($definition->getClass());
            $m = $this->getMetaDataDefinitionFromContainer($container);

            foreach($rClass->getMethods(\ReflectionMethod::IS_PUBLIC) as $rMethod) {
                foreach ($this->getAnnotationReader()->getMethodAnnotations($rMethod) as $a) {
                    $method = $rMethod->getName();
                    switch (true) {
                        case $a instanceof Velocity\Callback:
                            $m->addMethodCall('addCallback', ['.' === $a->value{0} ? ($type . '.' . $subType . '.' . $subSubType . $a->value) : $a->value, [new Reference($id), $method]]);
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

        foreach ($container->findTaggedServiceIds($this->getDefault('account_provider.tag')) as $id => $attributes) {
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
        $authenticationProviderDefinition = $container->getDefinition($this->getDefault(('authentication_provider.default.key')));
        $requestServiceDefinition         = $container->getDefinition($this->getDefault('request.key'));

        foreach ($container->findTaggedServiceIds($this->getDefault('client_provider.tag')) as $id => $attributes) {
            $attribute = array_shift($attributes);
            if ((isset($attribute['method']) && 'get' !== $attribute['method']) || isset($attribute['format'])) {
                $ref = new Definition($this->getDefault('decorated_client.class'), [new Reference($id), isset($attribute['method']) ? $attribute['method'] : 'get', isset($attribute['format']) ? $attribute['format'] : 'raw']);
                $refId = sprintf($this->getDefault('generated_client.key.pattern'), md5(uniqid()));
                $container->setDefinition($refId, $ref);
            } else {
                $refId = $id;
            }
            $authenticationProviderDefinition->addMethodCall('setClientProvider', [new Reference($refId)]);
            $requestServiceDefinition->addMethodCall('setClientProvider', [new Reference($refId)]);
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

        foreach ($container->findTaggedServiceIds($this->getDefault('migrator.tag')) as $id => $attributes) {
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