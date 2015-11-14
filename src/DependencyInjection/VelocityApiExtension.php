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

        $container->setParameter('app_tenant', $config['tenant']);
        $container->setParameter('app_variables', [
            'env'     => $container->hasParameter('app_env') ? $container->getParameter('app_env') : 'unknown',
            'front'   => $config['front'],
            'senders' => $config['senders'],
            'sdk'     => isset($config['sdk']) ? $config['sdk'] : null,
        ]);
        $container->setParameter('app_senders', $config['senders']);
        $container->setParameter('app_recipients', $config['recipients']);
        $container->setParameter('app_bundles', $config['bundles']);
        $container->setParameter('app_events', $config['events']);
        $container->setParameter('app_event_sets', $config['event_sets']);
        $container->setParameter('app_storages', $config['storages']);
        $container->setParameter('app_payment_provider_rules', $config['payment_provider_rules']);

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
        $loader->load('actions.yml');
        $loader->load('generators.yml');
        $loader->load('converters.yml');
        $loader->load('codeGenerators.yml');
        $loader->load('documentBuilders.yml');
        $loader->load('formatters.yml');
        $loader->load('paymentProviders.yml');
        $loader->load('jobs.yml');
    }
}
