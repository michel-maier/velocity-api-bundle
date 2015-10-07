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
    /**
     * @param string $type
     * @param Bag    $params
     * @param Bag    $context
     *
     * @throws \Exception
     */
    protected function sendSmsByType($type, Bag $params, Bag $context)
    {
        if ($params->has('bulk') && true === $params->get('bulk')) {
            $this->sendBulkSmsByType($type, $params, $context);
        } else {
            $this->sendSingleSmsByType($type, $params, $context);
        }
    }
    /**
     * @param string $type
     * @param Bag    $params
     * @param Bag    $context
     *
     * @throws \Exception
     */
    protected function sendBulkSmsByType($type, Bag $params, Bag $context)
    {
        $all = ($params->all() + $context->all() + ['recipients' => []]);
        $recipients = $all['recipients'];
        if (!count($recipients)) {
            throw $this->createRequiredException('No recipients specified for bulk sms');
        }

        foreach ($recipients as $recipientSms => $recipientName) {
            if (is_numeric($recipientSms)) {
                $recipientSms = $recipientName;
                $recipientName = $recipientSms;
            }
            if (!is_string($recipientName)) {
                $recipientName = $recipientSms;
            }
            $cleanedParams = $params->all();
            unset($cleanedParams['bulk'], $cleanedParams['recipients']);
            $cleanedParams['recipients'] = [$recipientSms => $recipientName];
            $this->sendSingleSmsByType($type, new Bag($cleanedParams), $context);
        }
    }
    /**
     * @param string $type
     * @param Bag    $params
     * @param Bag    $context
     */
    protected function sendSingleSmsByType($type, Bag $params, Bag $context)
    {
        $vars = $this->buildVariableBag($params, $context);

        $this->dispatch(
            'sms',
            new SmsEvent(
                $this->renderTemplate('sms/'.($type ? ($type.'/') : '').$vars->get('template').'.html.twig', $vars),
                $this->cleanRecipients($params->get('recipients')),
                array_map(function ($attachment) use ($vars) {
                    return $this->getAttachmentService()->build($attachment, $vars);
                }, $params->get('attachments', [])),
                $params->get('images', []),
                $params->get('sender', null),
                $params->get('options', [])
            )
        );
    }
    /**
     * @param array|mixed $recipients
     *
     * @return array
     *
     * @throws \Exception
     */
    protected function cleanRecipients($recipients)
    {
        if (!is_array($recipients)) {
            if (!is_string($recipients)) {
                throw $this->createMalformedException('Recipients must be a list or a string');
            }
            $recipients = [$recipients => $recipients];
        }

        $cleanedRecipients = [];

        foreach ($recipients as $k => $v) {
            unset($recipients[$k]);
            if (is_numeric($k)) {
                if (!is_string($v)) {
                    continue;
                }
                $cleanedRecipients[$v] = $v;
            } else {
                $cleanedRecipients[$k] = $v;
            }
        }

        if (!count($cleanedRecipients)) {
            throw $this->createRequiredException('No recipients specified');
        }

        return array_keys($cleanedRecipients);
    }
}
