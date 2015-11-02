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
use Velocity\Bundle\ApiBundle\Document;

/**
 * @author Olivier Hoareau <olivier@phppro.fr>
 *
 * @group document
 */
class DocumentTest extends PHPUnit_Framework_TestCase
{
    /**
     * @group unit
     */
    public function testConstruct()
    {
        $d = new Document('<a></a>', 'application/xml', 'x.xml');

        $this->assertNotNull($d);
        $this->assertEquals('<a></a>', $d->getContent());
        $this->assertEquals('application/xml', $d->getContentType());
        $this->assertEquals('x.xml', $d->getFileName());
    }
}
