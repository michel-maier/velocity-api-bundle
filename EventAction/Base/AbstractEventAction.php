<?php

/*
 * This file is part of the VELOCITY package.
 *
 * (c) PHPPRO <opensource@phppro.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Velocity\Bundle\ApiBundle\EventAction\Base;

use Velocity\Bundle\ApiBundle\Traits\ServiceTrait;
use Velocity\Bundle\ApiBundle\EventActionInterface;
use Velocity\Bundle\ApiBundle\Traits\ArrayizerTrait;
use Velocity\Bundle\ApiBundle\Traits\EventAction\ContextAwareTrait;

/**
 * Abstract Mail Event Action.
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
abstract class AbstractEventAction implements EventActionInterface
{
    use ServiceTrait;
    use ArrayizerTrait;
    use ContextAwareTrait;
}
