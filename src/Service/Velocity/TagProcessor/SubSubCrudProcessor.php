<?php
namespace Velocity\Bundle\ApiBundle\Service\Velocity\TagProcessor;

use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Sub Sub Crud Processor.
 *
 * @author Gabriele Santini <gab.santini@gmail.com>
 */
class SubSubCrudProcessor extends CrudProcessor
{
    /**
     * Process sub sub crud tags.
     *
     * @param ContainerBuilder $container
     *
     * @throws \Exception
     */
    public function process(ContainerBuilder $container)
    {
        foreach ($this->findVelocityTaggedServiceIds($container, 'crud.sub.sub') as $id => $attrs) {
            list($type, $subType, $subSubType) = array_slice(explode('.', $id), -3);
            $d = $container->getDefinition($id);
            $this->ensureDefinitionClassSet($d, 'crud.sub.sub');
            $d->addMethodCall('setTypes', [[$type, $subType, $subSubType]]);
            $params = array_shift($attrs) + ['id' => $type.'.'.$subType.'.'.$subSubType, 'repo' => $type];
            $this->addRepositorySetterCall($d, $this->getRepositoryId($params['repo']));
            $this->populateModelService($container, $id, $d, [$type, $subType, $subSubType]);
            $this->setCrudService(strtolower($params['id']), $id);
        }
    }
}
