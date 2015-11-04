<?php
namespace Velocity\Bundle\ApiBundle\Service\Velocity\TagProcessor;

use Symfony\Component\DependencyInjection\ContainerBuilder;

class VolatileProcessor extends AbstractTagProcessor
{
    /**
     * Process volatile tags.
     *
     * @param ContainerBuilder $container
     *
     * @throws \Exception
     */
    public function process(ContainerBuilder $container)
    {
        foreach ($this->findVelocityTaggedServiceIds($container, 'volatile') as $id => $attrs) {
            list($type) = array_slice(explode('.', $id), -1);
            $d = $container->getDefinition($id);
            $this->ensureDefinitionClassSet($d, 'volatile');
            $d->addMethodCall('setTypes', [[$type]]);
            $params = array_shift($attrs) + ['id' => $type];
            $this->populateModelService($container, $id, $d, [$type]);
            $this->setCrudService(strtolower($params['id']), $id);
        }
    }
}
