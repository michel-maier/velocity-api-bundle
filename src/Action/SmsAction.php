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
use Velocity\Bundle\ApiBundle\Action\Base\AbstractSmsAction;

/**
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
class SmsAction extends AbstractSmsAction
{
    /**
     * @param Bag $params
     * @param Bag $context
     *
     * @Velocity\Action("sms", description="send a sms")
     */
    public function sendSms(Bag $params, Bag $context)
    {
        $this->sendSmsByType(null, $params, $context);
    }
    /**
     * @param Bag $params
     * @param Bag $context
     *
     * @Velocity\Action("sms_user", description="send a user sms")
     */
    public function sendUserSms(Bag $params, Bag $context)
    {
        $params->setDefault('sender', $this->getDefaultSenderByTypeAndNature('sms_user', $params->get('template')));
        $params->setDefault('_locale', $this->getCurrentLocale());
        $params->setDefault('_tenant', $this->getTenant());
        $this->sendSmsByType('user', $params, $context);
    }
    /**
     * @param Bag $params
     * @param Bag $context
     *
     * @Velocity\Action("sms_admin", description="send an admin sms")
     */
    public function sendAdminSms(Bag $params, Bag $context)
    {
        $params->setDefault('recipients', $this->getDefaultRecipientsByTypeAndNature('sms_admins', $params->get('template')));
        $params->setDefault('sender', $this->getDefaultSenderByTypeAndNature('sms_admin', $params->get('template')));
        $params->setDefault('_tenant', $this->getTenant());
        $this->sendSmsByType('admin', $params, $context);
    }
}
