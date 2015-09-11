<?php

/*
 * This file is part of the VELOCITY package.
 *
 * (c) PHPPRO <opensource@phppro.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Velocity\Bundle\ApiBundle\Tests\Action;

use PHPUnit_Framework_TestCase;
use Velocity\Bundle\ApiBundle\Bag;
use PHPUnit_Framework_MockObject_MockObject;
use Velocity\Bundle\ApiBundle\Event\DocumentEvent;
use Velocity\Bundle\ApiBundle\Service\ArchiverService;
use Velocity\Bundle\ApiBundle\Action\ArchiveDocAction;

/**
 * @author Olivier Hoareau <olivier@phppro.fr>
 * @group eventAction
 * @group archiveDoc
 */
class ArchiveDocActionTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var ArchiveDocAction
     */
    protected $ea;
    /**
     * @var ArchiverService|PHPUnit_Framework_MockObject_MockObject
     */
    protected $archiver;
    /**
     *
     */
    public function setUp()
    {
        $this->archiver = $this->getMock('Velocity\\Bundle\\ApiBundle\\Service\\ArchiverService', [], [], '', false);
        $this->ea       = new ArchiveDocAction($this->archiver);
    }
    /**
     * @group unit
     */
    public function testArchiveDoc()
    {
        $doc = new \stdClass();

        $this->archiver
            ->expects($this->once())
            ->method('archive')
            ->with('stdClass', $doc)
            ->will($this->returnValue(null))
        ;

        $this->ea->archiveDoc(
            new Bag(),
            new Bag(['doc' => $doc, 'event' => new DocumentEvent($doc), 'eventName' => 'theEvent'])
        );
    }
}
