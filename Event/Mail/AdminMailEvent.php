<?php

/*
 * This file is part of the VELOCITY package.
 *
 * (c) PHPPRO <opensource@phppro.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Velocity\Bundle\ApiBundle\Event\Mail;

use Velocity\Bundle\ApiBundle\Event\MailEvent;

/**
 * Admin Mail Event.
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
class AdminMailEvent extends MailEvent
{
    /**
     * @return string
     */
    public function getTemplate()
    {
        return 'admin/'.parent::getTemplate();
    }
}