<?php
namespace Velocity\Bundle\ApiBundle\Service\Velocity\TagProcessor;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Velocity\Core\Traits\ServiceTrait;
use Symfony\Component\DependencyInjection\Definition;
use Velocity\Bundle\ApiBundle\Annotation\Callback;
use Symfony\Component\DependencyInjection\Reference;
use Velocity\Bundle\ApiBundle\Service\Velocity\IdsRegistry;
use Doctrine\Common\Annotations\AnnotationReader;
use Velocity\Bundle\ApiBundle\Annotation as Velocity;

/**
 * Superclass for all Tag Processors.
 *
 * @author Gabriele Santini <gab.santini@gmail.com>
 */
abstract class AbstractTagProcessor
{
    use ServiceTrait;

    /**
     * @var IdsRegistry
     */
    protected $idsRegistry;

    /**
     * @param IdsRegistry      $idsRegistry
     * @param AnnotationReader $reader
     */
    public function __construct($idsRegistry, $reader)
    {
        $this->setAnnotationReader($reader);
        $this->idsRegistry = $idsRegistry;

        $defaults = [
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

            // container keys patterns
            'generated_client.key.pattern' => 'app.client_%s',

            // classes
            'repo.class'              => 'Velocity\\Bundle\\ApiBundle\\Service\\RepositoryService',
            'crud.class'              => 'Velocity\\Bundle\\ApiBundle\\Service\\Base\\DocumentService',
            'crud.sub.class'          => 'Velocity\\Bundle\\ApiBundle\\Service\\Base\\SubDocumentService',
            'crud.sub.sub.class'      => 'Velocity\\Bundle\\ApiBundle\\Service\\Base\\SubSubDocumentService',
            'volatile.class'          => 'Velocity\\Bundle\\ApiBundle\\Service\\Base\\VolatileDocumentService',
            'volatile.sub.class'      => 'Velocity\\Bundle\\ApiBundle\\Service\\Base\\VolatileSubDocumentService',
            'volatile.sub.sub.class'  => 'Velocity\\Bundle\\ApiBundle\\Service\\Base\\VolatileSubSubDocumentService',
            'decorated_client.class'  => 'Velocity\\Bundle\\ApiBundle\\Service\\DecoratedClientService',

//             // interfaces
            'container_aware.interface' => 'Symfony\\Component\\DependencyInjection\\ContainerAwareInterface',
            'logger_aware.interface'    => 'Psr\\Log\\LoggerAwareInterface',

//             // namespaces
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
            'codeGenerator.tag'       => 'velocity.codeGenerator',

            'cruds_aware.tag'         => 'velocity.cruds_aware',
            'archiver.tag'            => 'velocity.archiver',
            'formatter.tag'           => 'velocity.formatter',
            'job.tag'                 => 'velocity.job',
            'storage.tag'             => 'velocity.storage',
            'document_builder.tag'    => 'velocity.document_builder',
            'repositories_aware.tag'  => 'velocity.repositories_aware',
        ];
        foreach ($defaults as $k => $v) {
            $this->setParameter('default_'.$k, $v);
        }
    }
    /**
     * @param ContainerBuilder $container
     */
    abstract public function process(ContainerBuilder $container);

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

        $rClass = new \ReflectionClass($d->getClass());

        foreach ($rClass->getMethods(\ReflectionMethod::IS_PUBLIC) as $rMethod) {
            foreach ($this->getAnnotationReader()->getMethodAnnotations($rMethod) as $a) {
                $method = $rMethod->getName();
                switch (true) {
                    case $a instanceof Callback:
                        $m->addMethodCall('addCallback', ['.' === $a->value{0} ? (join('.', $types).$a->value) : $a->value, [$this->ref($id), $method]]);
                        break;
                }
            }
        }

        return $this;
    }
    /**
     * @param AnnotationReader $reader
     *
     * @return $this
     */
    protected function setAnnotationReader(AnnotationReader $reader)
    {
        return $this->setService('annotationReader', $reader);
    }
    /**
     * @return AnnotationReader
     */
    protected function getAnnotationReader()
    {
        return $this->getService('annotationReader');
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
     * @param string $alias
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
     * @return Definition
     */
    protected function getMetaDataDefinitionFromContainer(ContainerBuilder $container)
    {
        return $container->getDefinition($this->getDefault('metaData.key'));
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
    protected function addEventDispatcherSetterCall(Definition $definition)
    {
        $definition->addMethodCall('setEventDispatcher', [$this->ref('event_dispatcher')]);
    }
    protected function addRepositorySetterCall(Definition $definition, $repoId)
    {
        $definition->addMethodCall('setRepository', [$this->ref($repoId)]);
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
     * @param string $name
     * @param string $id
     */
    protected function setCrudService($name, $id)
    {
        $this->idsRegistry->setCrud($name, $id);
    }
}
