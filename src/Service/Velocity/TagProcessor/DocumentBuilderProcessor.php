<?php
namespace Velocity\Bundle\ApiBundle\Service\Velocity\TagProcessor;

use Symfony\Component\DependencyInjection\ContainerBuilder;

class DocumentBuilderProcessor extends AbstractTagProcessor
{
    /**
     * Process document builder tags.
     *
     * @param ContainerBuilder $container
     *
     * @throws \Exception
     */
    public function process(ContainerBuilder $container)
    {        
        $dbDefinition = $container->getDefinition($this->getDefault('documentBuilder.key'));

        foreach ($this->findVelocityTaggedServiceIds($container, 'document_builder') as $id => $attributes) {
            foreach ($attributes as $params) {
                $type = $params['type'];
                unset($params['type']);
                $dbDefinition->addMethodCall('register', [$type, [$this->ref($id), 'build'], $params]);
            }
        }
    }
}
