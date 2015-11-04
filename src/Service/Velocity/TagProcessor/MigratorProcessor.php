<?php
namespace Velocity\Bundle\ApiBundle\Service\Velocity\TagProcessor;

use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Migrator Processor.
 *
 * @author Gabriele Santini <gab.santini@gmail.com>
 */
class MigratorProcessor extends AbstractTagProcessor
{
    /**
     * Process migrator tags.
     *
     * @param ContainerBuilder $container
     *
     * @throws \Exception
     */
    public function process(ContainerBuilder $container)
    {
        $containerAwareInterface    = $this->getDefault('container_aware.interface');
        $loggerAwareInterface       = $this->getDefault('logger_aware.interface');
        $migrationServiceDefinition = $container->getDefinition($this->getDefault('migration.key'));

        foreach ($this->findVelocityTaggedServiceIds($container, 'migrator') as $id => $attributes) {
            $d = $container->getDefinition($id);
            foreach ($attributes as $params) {
                $extension = $params['extension'];
                $rClass = new \ReflectionClass($d->getClass());
                if ($rClass->isSubclassOf($containerAwareInterface)) {
                    $this->addContainerSetterCall($d);
                }
                if ($rClass->isSubclassOf($loggerAwareInterface)) {
                    $this->addLoggerSetterCall($d);
                }
                $migrationServiceDefinition->addMethodCall('addMigrator', [$this->ref($id), $extension]);
            }
        }
    }
}
