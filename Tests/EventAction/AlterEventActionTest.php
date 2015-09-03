<?php

/*
 * This file is part of the VELOCITY package.
 *
 * (c) PHPPRO <opensource@phppro.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Velocity\Bundle\ApiBundle\Tests\EventAction;

use PHPUnit_Framework_TestCase;
use Velocity\Bundle\ApiBundle\EventAction\AlterEventAction;
use Velocity\Bundle\ApiBundle\RepositoryInterface;

/**
 * @author Olivier Hoareau <olivier@phppro.fr>
 * @group eventAction
 */
class AlterEventActionTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var AlterEventAction
     */
    protected $ea;
    /**
     *
     */
    public function setUp()
    {
        $this->ea = new AlterEventAction();
    }
    /**
     * @group unit
     * @group attachment
     */
    public function testRepositories()
    {
        /** @var RepositoryInterface $repo1 */
        $repo1 = $this->getMock('Velocity\\Bundle\\ApiBundle\\RepositoryInterface');
        /** @var RepositoryInterface $repo2 */
        $repo2 = $this->getMock('Velocity\\Bundle\\ApiBundle\\RepositoryInterface');

        $this->assertEquals([], $this->ea->getRepositories());

        $this->ea->addRepository('repo1', $repo1);
        $this->assertCount(1, $this->ea->getRepositories());

        $this->ea->addRepository('repo2', $repo2);
        $this->assertCount(2, $this->ea->getRepositories());

        $this->ea->addRepository('repo2', $repo2);
        $this->assertCount(2, $this->ea->getRepositories());

        $this->assertEquals($repo1, $this->ea->getRepository('repo1'));
        $this->assertEquals($repo2, $this->ea->getRepository('repo2'));

        $this->assertEquals(['repo1' => $repo1, 'repo2' => $repo2], $this->ea->getRepositories());
    }
}
