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
    public function generateRandomSha1String()
    {
        return $this->generateSha1String($this->generateRandomMd5String());
    }
    /**
     * @param string $string
     *
     * @return string
     *
     * @Velocity\Generator("sha1")
     */
    public function generateSha1String($string)
    {
        return sha1($string);
    }
    /**
     * @return string
     *
     * @Velocity\Generator("random_md5")
     */
    public function generateRandomMd5String()
    {
        return $this->generateMd5String($this->generateString());
    }
    /**
     * @param string $string
     *
     * @return string
     *
     * @Velocity\Generator("md5")
     */
    public function generateMd5String($string)
    {
        return md5($string);
    }
    /**
     * @param mixed $data
     *
     * @return string
     *
     * @Velocity\Generator("serialized")
     */
    public function generateSerializedString($data)
    {
        return serialize($data);
    }
    /**
     * @param mixed $data
     *
     * @return string
     *
     * @Velocity\Generator("fingerprint")
     */
    public function generateFingerPrintString($data)
    {
        if (is_string($data)) {
            return $this->generateMd5String($data);
        }

        return $this->generateMd5String($this->generateSerializedString($data));
    }
}
