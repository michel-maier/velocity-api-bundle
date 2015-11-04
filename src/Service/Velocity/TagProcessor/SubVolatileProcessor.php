<?php
namespace Velocity\Bundle\ApiBundle\Service\Velocity\TagProcessor;

use Symfony\Component\DependencyInjection\ContainerBuilder;

class SubVolatileProcessor extends AbstractTagProcessor
{
    /**
     * Process sub volatile tags.
     *
     * @param ContainerBuilder $container
     *
     * @throws \Exception
     */
    public function process(ContainerBuilder $container)
    {
        foreach ($this->findVelocityTaggedServiceIds($container, 'volatile.sub') as $id => $attrs) {
            list($type, $subType) = array_slice(explode('.', $id), -3);
            $d = $container->getDefinition($id);
            $this->ensureDefinitionClassSet($d, 'volatile.sub');
            $d->addMethodCall('setTypes', [[$type, $subType]]);
            $params = array_shift($attrs) + ['id' => $type.'.'.$subType];
            $this->populateModelService($container, $id, $d, [$type, $subType]);
            $this->setArrayParameterKey('crudServiceIds', strtolower($params['id']), $id);
        }
    }
}
