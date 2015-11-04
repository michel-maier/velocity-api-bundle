<?php
namespace Velocity\Bundle\ApiBundle\Service\Velocity\TagProcessor;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Velocity\Bundle\ApiBundle\Annotation as Velocity;

class CrudProcessor extends AbstractTagProcessor
{
    /**
     * Process crud tags.
     *
     * @param ContainerBuilder $container
     *
     * @throws \Exception
     */
    public function process(ContainerBuilder $container)
    {
        foreach ($this->findVelocityTaggedServiceIds($container, 'crud') as $id => $attributes) {
            list($type) = array_slice(explode('.', $id), -1);
            $d = $container->getDefinition($id);
            $this->ensureDefinitionClassSet($d, 'crud');
            $d->addMethodCall('setTypes', [[$type]]);
            $params = array_shift($attributes) + ['id' => $type, 'repo' => $type];
            $this->addRepositorySetterCall($d, $this->getRepositoryId($params['repo']));
            $this->populateModelService($container, $id, $d, [$type]);
            $this->setArrayParameterKey('crudServiceIds', strtolower($params['id']), $id);
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
        
        if (!$this->repositoryIds->has($alias)) {
            throw $this->createRequiredException("Unknown repository '%s'", $alias);
        }
    
        return $this->repositoryIds->get($alias);
    }
}