<?php
namespace Velocity\Bundle\ApiBundle\Service\Velocity\TagProcessor;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Repository Aware Processor.
 *
 * @author Gabriele Santini <gab.santini@gmail.com>
 */
class RepositoriesAwareProcessor extends AbstractTagProcessor
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
        foreach ($this->findVelocityTaggedServiceIds($container, 'repositories_aware') as $id => $attributes) {
            $d = $container->getDefinition($id);
            foreach ($attributes as $params) {
                $params += ['method' => 'addRepository'];
                foreach ($this->idsRegistry->getRepositories() as $repoAlias => $repoId) {
                    $d->addMethodCall($params['method'], [$repoAlias, new Reference($repoId)]);
                }
            }
        }
    }
}