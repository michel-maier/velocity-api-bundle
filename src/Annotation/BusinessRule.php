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
 * BusinessRule annotation
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 *
 * @Annotation
 * @Target("METHOD")
 */
final class BusinessRule extends Annotation
{
    /**
     * @var string
     */
    public $id = null;
    /**
     * @var string
     */
    public $name = null;
    /**
     * @var string
     */
    public $model = null;
    /**
     * @var string
     */
    public $operation = null;
    /**
     * @var string
     */
    public $when = 'before';
}
