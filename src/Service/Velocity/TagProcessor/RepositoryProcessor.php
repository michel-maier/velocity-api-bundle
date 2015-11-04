<?php
namespace Velocity\Bundle\ApiBundle\Service\Velocity\TagProcessor;

use Symfony\Component\DependencyInjection\ContainerBuilder;

class RepositoryProcessor extends AbstractTagProcessor
{
    /**
     * Process repository tags.
     *
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
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
            $this->setRepositoryId(strtolower($params['id']), $id);
        }
    }
    
    protected function setRepositoryId($name, $id)
    {
        $this->idsRegistry->setRepository($name, $id);
    }
}