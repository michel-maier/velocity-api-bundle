<?php

/*
 * This file is part of the VELOCITY package.
 *
 * (c) PHPPRO <opensource@phppro.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Velocity\Bundle\ApiBundle\EventAction;

use Velocity\Bundle\ApiBundle\Traits\ServiceAware;
use Velocity\Bundle\ApiBundle\Annotation as Velocity;
use Velocity\Bundle\ApiBundle\EventAction\Base\AbstractEventAction;

/**
 * @author Olivier Hoareau <olivier@tomboladirecte.com>
 */
class ContextEventAction extends AbstractEventAction
{
    /**
     * @Velocity\EventAction("dump_context", description="dump the context")
     */
    public function dump()
    {
        dump($context = $this->getContext());
    }
}
