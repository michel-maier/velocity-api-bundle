<?php

/*
 * This file is part of the VELOCITY package.
 *
 * (c) PHPPRO <opensource@phppro.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Velocity\Bundle\ApiBundle\Event\Sms;

use Velocity\Bundle\ApiBundle\Event\SmsEvent;

/**
 * User Sms Event.
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
class UserSmsEvent extends SmsEvent
{
    /**
     * @return string
     */
    public function getTemplate()
    {
        return 'user/'.parent::getTemplate();
    }
}