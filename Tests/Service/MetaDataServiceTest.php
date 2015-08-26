<?php

/*
 * This file is part of the VELOCITY package.
 *
 * (c) PHPPRO <opensource@phppro.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Velocity\Bundle\ApiBundle\Tests\Service;

use PHPUnit_Framework_TestCase;
use Velocity\Bundle\ApiBundle\Service\MetaDataService;

/**
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
class MetaDataServiceTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var MetaDataService
     */
    protected $s;
    /**
     *
     */
    public function setUp()
    {
        $this->s = new MetaDataService();
    }
    /**
     * @group unit
     */
    public function testModelClasses()
    {
        $this->assertEquals([], $this->s->getModels());
        $this->s->addModel('Model1', []);
        $this->assertEquals([
            'Model1' => [
                'embeddedReferences' => [],
                'embeddedReferenceLists' => [],
                'refreshes' => [],
                'generateds' => [],
                'ids' => [],
                'types' => [],
            ],
        ], $this->s->getModels());
    }
}
