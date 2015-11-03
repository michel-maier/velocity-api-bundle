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
 * Workflow annotation
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 *
 * @Annotation
 * @Target("PROPERTY")
 */
final class Workflow extends Annotation
{
    /**
     * @var string
     */
    public $id;
    /**
     * @var array
     */
    public $steps = [];
    /**
     * @var array
     */
    public $transitions = [];
}
