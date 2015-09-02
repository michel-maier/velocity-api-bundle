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

use Velocity\Bundle\ApiBundle\Annotation as Velocity;
use Velocity\Bundle\ApiBundle\EventAction\Base\AbstractEventAction;

/**
 * @author Olivier Hoareau <olivier@tomboladirecte.com>
 */
class ExceptionEventAction extends AbstractEventAction
{
    /**
     * @Velocity\EventAction("exception")
     */
    public function execute()
    {
        throw $this->createException(
            (int) $this->getContext()->getVariable('code'),
            $this->getContext()->getVariable('message')
        );
    }
}
