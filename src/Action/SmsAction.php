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
use Velocity\Bundle\ApiBundle\Event\SmsEvent;
use Velocity\Bundle\ApiBundle\Traits\ServiceAware;
use Velocity\Bundle\ApiBundle\Annotation as Velocity;
use Velocity\Bundle\ApiBundle\Action\Base\AbstractTextNotificationAction;

/**
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
class SmsAction extends AbstractTextNotificationAction
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
        $this->sendSmsByType('admin', $params, $context);
    }
    /**
     * @param string $type
     * @param Bag    $params
     * @param Bag    $context
     *
     * @throws \Exception
     */
    protected function sendSmsByType($type, Bag $params, Bag $context)
    {
        $vars = $this->buildVariableBag($params, $context);

        $this->dispatch(
            'sms',
            new SmsEvent(
                $this->renderTemplate('sms/'.($type ? ($type.'/') : '').$vars->get('template').'.html.twig', $vars),
                $params->get('recipients'),
                array_map(function ($attachment) use ($vars) {
                    return $this->getAttachmentService()->build($attachment, $vars);
                }, $params->get('attachments', [])),
                $params->get('images', []),
                $params->get('sender', null),
                $params->get('options', [])
            )
        );
    }
}
