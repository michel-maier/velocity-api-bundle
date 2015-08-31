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

use Velocity\Bundle\ApiBundle\Traits\ServiceTrait;
use Velocity\Bundle\ApiBundle\Annotation as Velocity;

/**
 * @author Olivier Hoareau <olivier@tomboladirecte.com>
 */
class ExceptionEventAction
{
    use ServiceTrait;
    /**
     * @param Context $context
     *
     * @Velocity\EventAction("exception", defaults={"code": 500, "message": "Unexpected exception"})
     */
    public function execute(Context $context)
    {
        throw $this->createException(
            $context->getVariable('message'),
            (int) $context->getVariable('code')
        );
    }
}
