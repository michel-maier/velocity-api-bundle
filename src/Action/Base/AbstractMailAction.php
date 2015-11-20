<?php

/*
 * This file is part of the VELOCITY package.
 *
 * (c) PHPPRO <opensource@phppro.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Velocity\Bundle\ApiBundle\Action\Base;

use Velocity\Core\Bag;
use Velocity\Bundle\ApiBundle\Event\MailEvent;
use Velocity\Bundle\ApiBundle\Traits\ServiceAware;
use Velocity\Bundle\ApiBundle\Annotation as Velocity;

/**
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
abstract class AbstractMailAction extends AbstractTextNotificationAction
{
    /**
     * @param string $type
     * @param Bag    $params
     * @param Bag    $context
     *
     * @throws \Exception
     */
    protected function sendMailByType($type, Bag $params, Bag $context)
    {
        if ($params->has('bulk') && true === $params->get('bulk')) {
            $this->sendBulkMailByType($type, $params, $context);
        } else {
            $this->sendSingleMailByType($type, $params, $context);
        }
    }
    /**
     * @param string $type
     * @param Bag    $params
     * @param Bag    $context
     *
     * @throws \Exception
     */
    protected function sendBulkMailByType($type, Bag $params, Bag $context)
    {
        $all = ($params->all() + $context->all() + ['recipients' => []]);
        $recipients = $all['recipients'];
        if (!count($recipients)) {
            throw $this->createRequiredException('No recipients specified for bulk email');
        }

        foreach ($recipients as $recipientEmail => $recipientName) {
            if (is_numeric($recipientEmail)) {
                $recipientEmail = $recipientName;
                $recipientName = $recipientEmail;
            }
            if (!is_string($recipientName)) {
                $recipientName = $recipientEmail;
            }
            $cleanedParams = $params->all();
            unset($cleanedParams['bulk'], $cleanedParams['recipients']);
            $cleanedParams['recipients'] = [$recipientEmail => $recipientName];
            $this->sendSingleMailByType($type, new Bag($cleanedParams), $context);
        }
    }
    /**
     * @param string $type
     * @param Bag    $params
     * @param Bag    $context
     */
    protected function sendSingleMailByType($type, Bag $params, Bag $context)
    {
        $vars     = $this->buildVariableBag($params, $context);
        $template = ($type ? ($type.'/') : '').$vars->get('template');

        $this->dispatch(
            'mail',
            new MailEvent(
                $this->renderInlineTemplate($this->getTranslator()->trans(str_replace('/', '_', $template), [], 'mail'), $vars),
                $this->renderTemplate('mail/'.$template.'.html.twig', $vars),
                $this->cleanRecipients($vars->get('recipients')),
                array_map(function ($attachment) use ($vars) {
                    return $this->getAttachmentService()->build($attachment, $vars->all());
                }, $params->get('attachments', [])),
                $vars->get('images', []),
                $vars->get('sender', null),
                $vars->get('options', [])
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

        return $cleanedRecipients;
    }
}
