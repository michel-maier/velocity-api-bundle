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

use Velocity\Core\Bag;
use Velocity\Bundle\ApiBundle\Annotation as Velocity;
use Velocity\Bundle\ApiBundle\Action\Base\AbstractAction;

/**
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
class ExceptionAction extends AbstractAction
{
    /**
     * @param Bag $params
     *
     * @Velocity\Action("exception", description="fail")
     *
     * @throws \Exception
     */
    public function execute(Bag $params)
    {
        throw $this->createException((int) $params->get('code'), (string) $params->get('message'));
    }
}
