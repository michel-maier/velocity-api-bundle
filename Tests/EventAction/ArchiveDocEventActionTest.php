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
use PHPUnit_Framework_MockObject_MockObject;
use Velocity\Bundle\ApiBundle\Event\DocumentEvent;
use Velocity\Bundle\ApiBundle\EventAction\Context;
use Velocity\Bundle\ApiBundle\Service\ArchiverService;
use Velocity\Bundle\ApiBundle\EventAction\ArchiveDocEventAction;

/**
 * @author Olivier Hoareau <olivier@phppro.fr>
 * @group eventAction
 * @group archiveDoc
 */
class ArchiveDocEventActionTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var ArchiveDocEventAction
     */
    protected $ea;
    /**
     * @var Context|PHPUnit_Framework_MockObject_MockObject
     */
    protected $context;
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
        $this->context  = new Context();
        $this->ea       = new ArchiveDocEventAction($this->archiver);

        $this->ea->setContext($this->context);
    }
    /**
     * @group unit
     */
    public function testArchiveDoc()
    {
        $doc = new \stdClass();

        $this->context->setCurrentEventVariables(new DocumentEvent($doc), 'theEvent');

        $this->archiver
            ->expects($this->once())
            ->method('archive')
            ->with('stdClass', $doc)
            ->will($this->returnValue(null))
        ;

        $this->ea->archiveDoc();
    }
}
