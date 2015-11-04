<?php
namespace Velocity\Bundle\ApiBundle\Service\Velocity\TagProcessor;

use Symfony\Component\DependencyInjection\ContainerBuilder;

class StorageProcessor extends AbstractTagProcessor
{
    /**
     * Process storage tags.
     *
     * @param ContainerBuilder $container
     *
     * @throws \Exception
     */
    public function process(ContainerBuilder $container)
    {        
        $storageDefinition = $container->getDefinition($this->getDefault('storage.key'));

        foreach ($this->findVelocityTaggedServiceIds($container, 'storage') as $id => $attributes) {
            foreach ($attributes as $params) {
                $params += ['name' => null, 'mount' => '/'];
                $storageDefinition->addMethodCall('mount', [$params['name'], $params['mount'], $this->ref($id)]);
            }
        }
    }
}
