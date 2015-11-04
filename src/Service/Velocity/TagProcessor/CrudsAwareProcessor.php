<?php
namespace Velocity\Bundle\ApiBundle\Service\Velocity\TagProcessor;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Crud Aware Processor.
 *
 * @author Gabriele Santini <gab.santini@gmail.com>
 */
class CrudsAwareProcessor extends AbstractTagProcessor
{
    /**
     * Process repositories aware tags.
     *
     * @param ContainerBuilder $container
     *
     * @throws \Exception
     */
    public function process(ContainerBuilder $container)
    {
        foreach ($this->findVelocityTaggedServiceIds($container, 'cruds_aware') as $id => $attributes) {
            $d = $container->getDefinition($id);
            foreach ($attributes as $params) {
                $params += ['method' => 'addCrudService'];
                foreach ($this->idsRegistry->getCruds() as $serviceAlias => $serviceId) {
                    $d->addMethodCall($params['method'], [$serviceAlias, new Reference($serviceId)]);
                }
            }
        }
    }
}
