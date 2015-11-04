<?php
namespace Velocity\Bundle\ApiBundle\Service\Velocity\TagProcessor;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

/**
 * Provider Client Processor.
 *
 * @author Gabriele Santini <gab.santini@gmail.com>
 */
class ProviderClientProcessor extends AbstractTagProcessor
{
    /**
     * Process provider account tags.
     *
     * @param ContainerBuilder $container
     *
     * @throws \Exception
     */
    public function process(ContainerBuilder $container)
    {
        $authenticationProviderDefinition = $container->getDefinition($this->getDefault(('authentication_provider.default.key')));
        $requestServiceDefinition         = $container->getDefinition($this->getDefault('request.key'));

        foreach ($this->findVelocityTaggedServiceIds($container, 'client_provider') as $id => $attrs) {
            $attribute = array_shift($attrs);
            $refId = $id;
            if ((isset($attribute['method']) && 'get' !== $attribute['method']) || isset($attribute['format'])) {
                $ref = new Definition(
                    $this->getDefault(
                        'decorated_client.class'
                    ),
                    [$this->ref($id), isset($attribute['method']) ?
                        $attribute['method'] :
                        'get', isset($attribute['format']) ? $attribute['format'] : 'raw', ]
                );
                $refId = sprintf($this->getDefault('generated_client.key.pattern'), md5(uniqid()));
                $container->setDefinition($refId, $ref);
            }
            $authenticationProviderDefinition->addMethodCall('setClientProvider', [$this->ref($refId)]);
            $requestServiceDefinition->addMethodCall('setClientProvider', [$this->ref($refId)]);
        }
    }
}
