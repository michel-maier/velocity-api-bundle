<?php
namespace Velocity\Bundle\ApiBundle\Service\Velocity\TagProcessor;

use Symfony\Component\DependencyInjection\ContainerBuilder;

class SubSubVolatileProcessor extends AbstractTagProcessor
{
    /**
     * Process sub sub volatile tags.
     *
     * @param ContainerBuilder $container
     *
     * @throws \Exception
     */
    public function process(ContainerBuilder $container)
    {
        foreach ($this->findVelocityTaggedServiceIds($container, 'volatile.sub.sub') as $id => $attrs) {
            list($type, $subType, $subSubType) = array_slice(explode('.', $id), -3);
            $d = $container->getDefinition($id);
            $this->ensureDefinitionClassSet($d, 'volatile.sub.sub');
            $d->addMethodCall('setTypes', [[$type, $subType, $subSubType]]);
            $params = array_shift($attrs) + ['id' => $type.'.'.$subType.'.'.$subSubType];
            $this->populateModelService($container, $id, $d, [$type, $subType, $subSubType]);
            $this->setArrayParameterKey('crudServiceIds', strtolower($params['id']), $id);
        }
    }
}
