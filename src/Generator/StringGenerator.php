<?php

/*
 * This file is part of the VELOCITY package.
 *
 * (c) PHPPRO <opensource@phppro.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Velocity\Bundle\ApiBundle\Generator;

use Velocity\Core\Traits\ServiceTrait;
use Velocity\Bundle\ApiBundle\Annotation as Velocity;

/**
 * String Generator Action.
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
class StringGenerator
{
    use ServiceTrait;
    /**
     * @return string
     *
     * @Velocity\Generator("random_string")
     */
    public function generateString()
    {
        return rand(0, 1000).microtime(true).rand(rand(0, 100), 10000);
    }
    /**
     * @return string
     *
     * @Velocity\Generator("random_sha1")
     */
    public function generateSha1String()
    {
        return sha1($this->generateMd5String());
    }
    /**
     * @return string
     *
     * @Velocity\Generator("random_md5")
     */
    public function generateMd5String()
    {
        return md5($this->generateString());
    }
}
