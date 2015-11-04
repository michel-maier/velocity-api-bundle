<?php
namespace Velocity\Bundle\ApiBundle\Service\Velocity\TagProcessor;

use Symfony\Component\DependencyInjection\ContainerBuilder;

class SubCrudProcessor extends CrudProcessor
{
    /**
     * Process sub crud tags.
     *
     * @param ContainerBuilder $container
     *
     * @throws \Exception
     */
    public function process(ContainerBuilder $container)
    {
            foreach ($this->findVelocityTaggedServiceIds($container, 'crud.sub') as $id => $attrs) {
            list($type, $subType) = array_slice(explode('.', $id), -2);
            $d = $container->getDefinition($id);
            $this->ensureDefinitionClassSet($d, 'crud.sub');
            $d->addMethodCall('setTypes', [[$type, $subType]]);
            $params = array_shift($attrs) + ['id' => $type.'.'.$subType, 'repo' => $type];
            $this->addRepositorySetterCall($d, $this->getRepositoryId($params['repo']));
            $this->populateModelService($container, $id, $d, [$type, $subType]);
            $this->setArrayParameterKey('crudServiceIds', strtolower($params['id']), $id);
        }
    }
}