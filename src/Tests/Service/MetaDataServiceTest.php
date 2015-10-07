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
use Velocity\Bundle\ApiBundle\Service\GeneratorService;
use Velocity\Bundle\ApiBundle\Service\MetaDataService;
use Velocity\Bundle\ApiBundle\Service\StorageService;

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
     * @var StorageService|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storage;
    /**
     * @var GeneratorService|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $generator;
    /**
     *
     */
    public function setUp()
    {
        $this->storage   = $this->getMock('Velocity\\Bundle\\ApiBundle\\Service\\StorageService', [], [], '', false);
        $this->generator = $this->getMock('Velocity\\Bundle\\ApiBundle\\Service\\GeneratorService', [], [], '', false);
        $this->s = new MetaDataService($this->storage, $this->generator);
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
                'storages' => [],
                'ids' => [],
                'types' => [],
                'referenceLists' => [],
            ],
        ], $this->s->getModels());
    }
}
