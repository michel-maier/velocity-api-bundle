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
use Velocity\Bundle\ApiBundle\Traits\ServiceAware;
use Velocity\Bundle\ApiBundle\Annotation as Velocity;
use Velocity\Bundle\ApiBundle\Action\Base\AbstractLetterAction;

/**
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
class LetterAction extends AbstractLetterAction
{
    /**
     * @param Bag $params
     * @param Bag $context
     *
     * @Velocity\Action("letter", description="send a letter")
     */
    public function sendLetter(Bag $params, Bag $context)
    {
        $this->sendLetterByType(null, $params, $context);
    }
}
