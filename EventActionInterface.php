<?php

/*
 * This file is part of the VELOCITY package.
 *
 * (c) PHPPRO <opensource@phppro.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Velocity\Bundle\ApiBundle;

use Velocity\Bundle\ApiBundle\EventAction\Context;

/**
 * Event Action Interface.
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
interface EventActionInterface
{
    /**
     * @param Context $context
     *
     * @return $this
     */
    public function setContext(Context $context);
    /**
     * @return Context
     */
    public function getContext();
}
