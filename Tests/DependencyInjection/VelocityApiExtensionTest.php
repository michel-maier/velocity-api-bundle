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
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Velocity\Bundle\ApiBundle\DependencyInjection\VelocityApiExtension;

/**
 * @author Olivier Hoareau <olivier@phppro.fr>
 *
 * @group velocity_api_extension
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
    public function testLoadForEventsSectionSetAppropriateEvents()
    {
        $c = $this->load([[
            'events' => [
                'user_created' => [
                    'actions' => [
                      'inc_kpi(users)',
                      'mail_user',
                      'mail_admin',
                      'fire',
                    ],
                ],
            ]
        ]]);

        $this->assertTrue($c->hasParameter('app_events'));
        $this->assertEquals(
            [
                'user_created' => [
                    'actions' => [
                        ['action' => 'inc_kpi', 'params' => ['value' => 'users']],
                        ['action' => 'mail_user',],
                        ['action' => 'mail_admin',],
                        ['action' => 'fire',],
                    ],
                ],
            ],
            $c->getParameter('app_events')
        );
    }
}
