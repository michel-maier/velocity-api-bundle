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
    /**
     *
     */
    public function setUp()
    {
        $this->e = new VelocityApiExtension();
        $this->container = new ContainerBuilder();
    }
    /**
     * @group unit
     */
    public function testLoadForEmptyConfigThrowException()
    {
        $this->setExpectedException(
            'Symfony\\Component\\Config\\Definition\\Exception\\InvalidConfigurationException',
            'The child node "tenant" at path "velocity_api" must be configured.'
        );
        $this->assertNotNull($this->load([]));
    }
    /**
     * @group unit
     */
    public function testLoadForModelsSectionSetAppropriateParameters()
    {
        $c = $this->load([['tenant' => 'test', 'apps' => ['front' => ['name' => 'a', 'url' => 'b']], 'bundles' => ['AppBundle']]]);

        $this->assertTrue($c->hasParameter('app_bundles'));
        $this->assertEquals(['AppBundle'], $c->getParameter('app_bundles'));
    }
    /**
     * @group unit
     */
    public function testLoadForRecipientsSectionSetAppropriateParameters()
    {
        $c = $this->load([
            [
                'tenant' => 'test',
                'apps' => ['front' => ['name' => 'a', 'url' => 'b']],
                'recipients' => [
                    'admins' => [
                        'a@b.com' => ['name' => 'A B'],
                        'e@f.com' => ['name' => 'E F', 'envs' => ['x', 'y'], 'types' => ['z']],
                    ],
                ],
            ],
        ]);

        $this->assertTrue($c->hasParameter('app_recipients_admins'));
        $this->assertEquals([
            'a@b.com' => ['name' => 'A B', 'envs' => ['*'], 'types' => ['*']],
            'e@f.com' => ['name' => 'E F', 'envs' => ['x', 'y'], 'types' => ['z']],
        ], $c->getParameter('app_recipients_admins'));
    }
    /**
     * @group unit
     */
    public function testLoadForEventsSectionSetAppropriateEvents()
    {
        $c = $this->load([[
            'tenant' => 'test',
            'apps' => ['front' => ['name' => 'a', 'url' => 'b']],
            'events' => [
                'user_created' => [
                    'actions' => [
                      ['action' => 'inc_kpi', 'value' => 'users'],
                      ['action' => 'mail_user'],
                      ['action' => 'mail_admin'],
                      ['action' => 'fire'],
                    ],
                ],
            ],
        ], ]);

        $this->assertTrue($c->hasParameter('app_events'));
        $this->assertEquals(
            [
                'user_created' => [
                    'actions' => [
                        ['action' => 'inc_kpi', 'params' => ['value' => 'users']],
                        ['action' => 'mail_user', 'params' => []],
                        ['action' => 'mail_admin', 'params' => []],
                        ['action' => 'fire', 'params' => []],
                    ],
                ],
            ],
            $c->getParameter('app_events')
        );
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
}
