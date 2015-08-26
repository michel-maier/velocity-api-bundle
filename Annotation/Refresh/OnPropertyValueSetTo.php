<?php

/*
 * This file is part of the VELOCITY package.
 *
 * (c) PHPPRO <opensource@phppro.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Velocity\Bundle\ApiBundle\Annotation\Refresh;

use Doctrine\Common\Annotations\Annotation;

/**
 * OnPropertyValueSetTo Refresh annotation
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 *
 * @Annotation
 * @Target("PROPERTY")
 */
final class OnPropertyValueSetTo extends Annotation
{
    public $unset = false;
}
