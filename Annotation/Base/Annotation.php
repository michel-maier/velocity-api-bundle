<?php

/*
 * This file is part of the VELOCITY package.
 *
 * (c) PHPPRO <opensource@phppro.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Velocity\Bundle\ApiBundle\Annotation\Base;

use Velocity\Bundle\ApiBundle\Annotation\AnnotationInterface;
use Doctrine\Common\Annotations\Annotation as DoctrineAnnotation;

/**
 * Annotation
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
abstract class Annotation extends DoctrineAnnotation implements AnnotationInterface
{
}