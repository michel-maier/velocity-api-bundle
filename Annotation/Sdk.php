<?php

/*
 * This file is part of the VELOCITY package.
 *
 * (c) PHPPRO <opensource@phppro.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Velocity\Bundle\ApiBundle\Annotation;

use Velocity\Bundle\ApiBundle\Annotation\Base\Annotation;

/**
 * Sdk annotation
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 *
 * @Annotation
 * @Target("METHOD")
 */
final class Sdk extends Annotation
{
    /**
     * @var string
     */
    public $service;
    /**
     * @var string
     */
    public $method;
    /**
     * @var string
     */
    public $type;
    /**
     * @var array
     */
    public $params = [];
}
