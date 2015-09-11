<?php

/*
 * This file is part of the VELOCITY package.
 *
 * (c) PHPPRO <opensource@phppro.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Velocity\Bundle\ApiBundle\Action;

use Velocity\Bundle\ApiBundle\Bag;
use Velocity\Bundle\ApiBundle\Traits\ServiceAware;
use Velocity\Bundle\ApiBundle\Annotation as Velocity;
use Velocity\Bundle\ApiBundle\Action\Base\AbstractAction;

/**
 * @author Olivier Hoareau <olivier@tomboladirecte.com>
 */
class ContextAction extends AbstractAction
{
    /**
     * @param Bag $params
     * @param Bag $context
     *
     * @Velocity\Action("dump_context", description="dump the context")
     */
    public function dump(Bag $params, Bag $context)
    {
        unset($params);
        dump($context);
    }
}
