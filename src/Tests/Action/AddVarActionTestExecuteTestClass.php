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

/**
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
class AddVarActionTestExecuteTestClass
{
    /**
     * @var bool
     */
    public $called = false;
    /**
     *
     */
    public function m1()
    {
        $this->called = true;
    }
}
