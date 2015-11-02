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
use Velocity\Bundle\ApiBundle\GenericDocument;

/**
 * @author Olivier Hoareau <olivier@phppro.fr>
 *
 * @group document
 */
class GenericDocumentTest extends PHPUnit_Framework_TestCase
{
    /**
     * @group unit
     */
    public function testConstruct()
    {
        $d = new GenericDocument('<a></a>', 'xml');

        $this->assertNotNull($d);
        $this->assertEquals('<a></a>', $d->getContent());
        $this->assertEquals('application/xml', $d->getContentType());
    }
    /**
     * @group unit
     */
    public function testConstructForUnknownFormatLoadDefaultFormat()
    {
        $d = new GenericDocument('xyz', 'xyz');

        $this->assertEquals('application/octet-stream', $d->getContentType());
    }
}
