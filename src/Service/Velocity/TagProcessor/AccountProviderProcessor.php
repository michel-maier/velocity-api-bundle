<?php
namespace Velocity\Bundle\ApiBundle\Service\Velocity\TagProcessor;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Velocity\Bundle\ApiBundle\Annotation as Velocity;

/**
 * Account Provider Tag Processor.
 *
 * @author Gabriele Santini <gab.santini@gmail.com>
 */
class AccountProviderProcessor extends AbstractTagProcessor
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
        $userProviderDefinition = $container->getDefinition($this->getDefault('user_provider.default.key'));

        foreach ($this->findVelocityTaggedServiceIds($container, 'account_provider') as $id => $attrs) {
            foreach ($attrs as $params) {
                $type   = isset($params['type']) ? $params['type'] : 'default';
                $method = isset($params['method']) ? $params['method'] : 'get';
                $format = isset($params['format']) ? $params['format'] : 'plain';
                $userProviderDefinition->addMethodCall('setAccountProvider', [$this->ref($id), $type, $method, $format]);
            }
        }
    }
}
