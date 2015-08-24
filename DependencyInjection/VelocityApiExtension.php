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
     * @param array $configs
     * @param ContainerBuilder $container
     *
     * @return void
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('app_models_bundles', $config['models']['bundles']);

        foreach($config['emails'] as $type => $emails) {
            $container->setParameter(sprintf('app_emails_%s', $type), $emails);
        }

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('parameters.yml');
        $loader->load('services/common.yml');
        $loader->load('services/repositories.yml');
        $loader->load('services/crud.yml');
        $loader->load('security.yml');
        $loader->load('forms.yml');
        $loader->load('commands.yml');
        $loader->load('validators.yml');
        $loader->load('migrators.yml');
        $loader->load('listeners.yml');

        $ecld = $container->getDefinition('velocity.listener.eventConverter');

        foreach($config['events'] as $eventName => $trackers) {
            foreach($trackers as $tracker) {
                switch($tracker) {
                    case 'mail_user':
                        $ecld->addTag('kernel.event_listener', ['event' => $eventName, 'method' => 'mailUser']);
                        break;
                    case 'sms_user':
                        $ecld->addTag('kernel.event_listener', ['event' => $eventName, 'method' => 'smsUser']);
                        break;
                    case 'mail_admin':
                        $ecld->addTag('kernel.event_listener', ['event' => $eventName, 'method' => 'mailAdmin']);
                        break;
                    case 'sms_admin':
                        $ecld->addTag('kernel.event_listener', ['event' => $eventName, 'method' => 'smsAdmin']);
                        break;
                    case 'fire':
                        $ecld->addTag('kernel.event_listener', ['event' => $eventName, 'method' => 'fireAndForget']);
                        break;
                    default:
                        throw new \RuntimeException(sprintf("Unsupported event track type '%s'", $tracker), 500);
                }
            }
        }

    }
}
