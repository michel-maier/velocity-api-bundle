<?php

/*
 * This file is part of the VELOCITY package.
 *
 * (c) PHPPRO <opensource@phppro.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Velocity\Bundle\ApiBundle\Tests\Storage;

use PHPUnit_Framework_TestCase;
use PHPUnit_Framework_MockObject_MockObject;
use Symfony\Component\Filesystem\Filesystem;
use Velocity\Bundle\ApiBundle\Storage\FileStorage;

/**
 * @author Olivier Hoareau <olivier@phppro.fr>
 *
 * @group storage
 */
class FileStorageTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var FileStorage
     */
    protected $s;
    /**
     * @var string
     */
    protected $tmpDir;
    /**
     * @var Filesystem|PHPUnit_Framework_MockObject_MockObject
     */
    protected $fs;
    /**
     *
     */
    public function setUp()
    {
        $this->fs     = $this->getMock('Symfony\\Component\\Filesystem\\Filesystem', [], [], '', false);
        $this->tmpDir = tempnam(sys_get_temp_dir(), 'test-'.uniqid());
        $this->s      = new FileStorage($this->tmpDir, $this->fs);
    }
    /**
     * @group unit
     */
    public function testSet()
    {
        $this->fs->expects($this->once())->method('mkdir')->with($this->tmpDir.'/a');
        $this->fs->expects($this->once())->method('dumpFile')->with($this->tmpDir.'/a/b.txt', 'xyz');
        $this->s->set('a/b.txt', 'xyz');
    }
}
