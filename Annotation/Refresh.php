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

use Doctrine\Common\Annotations\Annotation;

/**
 * Refresh annotation
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 *
 * @Annotation
 * @Target("PROPERTY")
 */
final class Refresh extends Annotation
{
}