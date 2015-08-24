<?php

/*
 * This file is part of the VELOCITY package.
 *
 * (c) PHPPRO <opensource@phppro.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Velocity\Bundle\ApiBundle\Tests\DependencyInjection;

use PHPUnit_Framework_TestCase;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Velocity\Bundle\ApiBundle\DependencyInjection\VelocityApiExtension;

/**
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
class VelocityApiExtensionTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var VelocityApiExtension
     */
    protected $e;
    /**
     * @var ContainerBuilder
     */
    protected $container;

    public function setUp()
    {
        $this->e = new VelocityApiExtension();
        $this->container = new ContainerBuilder();

        $this->container->setDefinition('event_dispatcher', new Definition(
            'Symfony\\Component\\EventDispatcher\\EventDispatcher', []
        ));
        $this->container->setDefinition('velocity.listener.eventConverter', new Definition(
            'Velocity\\Bundle\\ApiBundle\\Listener\\EventConverterListener',
            [new Reference('event_dispatcher')]
        ));
    }
    /**
     * @param $config
     * @param ContainerBuilder|null $container
     *
     * @return ContainerBuilder
     */
    protected function load($config, ContainerBuilder $container = null)
    {
        if (null === $container) {
            $container = $this->container;
        }

        $this->e->load($config, $container);

        return $container;
    }
    /**
     * @group unit
     */
    public function testLoadForEmptyConfigDoNotThrowException()
    {
        $this->assertNotNull($this->load([]));
    }
    /**
     * @group unit
     */
    public function testLoadForModelsSectionSetAppropriateParameters()
    {
        $c = $this->load([['models' => ['bundles' => ['AppBundle']]]]);

        $this->assertTrue($c->hasParameter('app_models_bundles'));
        $this->assertEquals(['AppBundle'], $c->getParameter('app_models_bundles'));
    }
    /**
     * @group unit
     */
    public function testLoadForEmailsSectionSetAppropriateParameters()
    {
        $c = $this->load([[
            'emails' => [
                'admins' => [
                    'a@b.com' => 'A B',
                    'c@d.com' => [
                        'name' => 'C D'
                    ],
                    'e@f.com' => [
                        'name' => 'E F',
                        'envs' => ['x', 'y'],
                        'types' => ['z'],
                    ]
                ]
            ]
        ]]);

        $this->assertTrue($c->hasParameter('app_emails_admins'));
        $this->assertEquals([
            'a@b.com' => ['name' => 'A B', 'envs' => ['*'], 'types' => ['*']],
            'c@d.com' => ['name' => 'C D', 'envs' => ['*'], 'types' => ['*']],
            'e@f.com' => ['name' => 'E F', 'envs' => ['x', 'y'], 'types' => ['z']],
        ],$c->getParameter('app_emails_admins'));
    }
    /**
     * @group unit
     */
    public function testLoadForEventsSectionWithUnknownTrackTypeThrowException()
    {
        $this->setExpectedException('RuntimeException', "Unsupported event track type 'unknown'", 500);

        $this->load([[
            'events' => [
                'a' => ['unknown'],
            ]
        ]]);
    }
    /**
     * @group unit
     */
    public function testLoadForEventsSectionSetAppropriateEvents()
    {
        $c = $this->load([[
            'events' => [
                'a' => ['mail_user'],
                'b' => ['mail_admin'],
                'c' => ['mail_user', 'mail_admin'],
                'd' => [],
                'e' => ['mail_admin', 'fire'],
                'f' => ['sms_user', 'sms_admin']
            ]
        ]]);

        $ecld = $c->getDefinition('velocity.listener.eventConverter');

        $this->assertEquals(
            [
                'kernel.event_listener' => [
                    ['event' => 'a', 'method' => 'mailUser'],
                    ['event' => 'b', 'method' => 'mailAdmin'],
                    ['event' => 'c', 'method' => 'mailUser'],
                    ['event' => 'c', 'method' => 'mailAdmin'],
                    ['event' => 'e', 'method' => 'mailAdmin'],
                    ['event' => 'e', 'method' => 'fireAndForget'],
                    ['event' => 'f', 'method' => 'smsUser'],
                    ['event' => 'f', 'method' => 'smsAdmin'],
                ]
            ],
            $ecld->getTags()
        );
    }
}
