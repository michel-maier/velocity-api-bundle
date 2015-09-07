<?php

/*
 * This file is part of the VELOCITY package.
 *
 * (c) PHPPRO <opensource@phppro.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Velocity\Bundle\ApiBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * VelocityApiExtension.
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
class VelocityApiExtension extends Extension
{
    /**
     * @param array            $configs
     * @param ContainerBuilder $container
     *
     * @return void
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('app_variables', [
            'front'   => $config['front'],
            'senders' => $config['senders'],
            'sdk'     => isset($config['sdk']) ? $config['sdk'] : null,
        ]);
        $container->setParameter('app_models_bundles', $config['models']['bundles']);
        $container->setParameter('app_events', $config['events']);

        foreach ($config['recipients'] as $type => $emails) {
            $container->setParameter(sprintf('app_recipients_%s', $type), $emails);
        }

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('parameters.yml');
        $loader->load('services/common.yml');
        $loader->load('services/repositories.yml');
        $loader->load('services/crud.yml');
        $loader->load('security.yml');
        $loader->load('forms/types.yml');
        $loader->load('forms/typeGuessers.yml');
        $loader->load('commands.yml');
        $loader->load('validators.yml');
        $loader->load('migrators.yml');
        $loader->load('listeners.yml');
        $loader->load('eventActions.yml');
        $loader->load('jobs.yml');
    }
}
