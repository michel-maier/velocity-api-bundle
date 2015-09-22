<?php

/*
 * This file is part of the VELOCITY package.
 *
 * (c) PHPPRO <opensource@phppro.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Velocity\Bundle\ApiBundle;

/**
 * Document Interface.
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
interface DocumentInterface
{
    /**
     * @return mixed
     */
    public function getContent();

    /**
     * @return string
     */
    public function getContentType();
    /**
     * @return string
     */
    public function getFileName();
}
