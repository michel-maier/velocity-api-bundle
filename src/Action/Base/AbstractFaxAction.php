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
use Velocity\Bundle\ApiBundle\Event\FaxEvent;
use Velocity\Bundle\ApiBundle\Traits\ServiceAware;
use Velocity\Bundle\ApiBundle\Annotation as Velocity;

/**
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
abstract class AbstractFaxAction extends AbstractTextNotificationAction
{
    /**
     * @param string $type
     * @param Bag    $params
     * @param Bag    $context
     *
     * @throws \Exception
     */
    protected function sendFaxByType($type, Bag $params, Bag $context)
    {
        if ($params->has('bulk') && true === $params->get('bulk')) {
            $this->sendBulkFaxByType($type, $params, $context);
        } else {
            $this->sendSingleFaxByType($type, $params, $context);
        }
    }
    /**
     * @param string $type
     * @param Bag    $params
     * @param Bag    $context
     *
     * @throws \Exception
     */
    protected function sendBulkFaxByType($type, Bag $params, Bag $context)
    {
        $all = ($params->all() + $context->all() + ['recipients' => []]);
        $recipients = $all['recipients'];
        if (!count($recipients)) {
            throw $this->createRequiredException('No recipients specified for bulk fax');
        }

        foreach ($recipients as $recipientFax => $recipientName) {
            if (is_numeric($recipientFax)) {
                $recipientFax = $recipientName;
                $recipientName = $recipientFax;
            }
            if (!is_string($recipientName)) {
                $recipientName = $recipientFax;
            }
            $cleanedParams = $params->all();
            unset($cleanedParams['bulk'], $cleanedParams['recipients']);
            $cleanedParams['recipients'] = [$recipientFax => $recipientName];
            $this->sendSingleFaxByType($type, new Bag($cleanedParams), $context);
        }
    }
    /**
     * @param string $type
     * @param Bag    $params
     * @param Bag    $context
     */
    protected function sendSingleFaxByType($type, Bag $params, Bag $context)
    {
        $vars = $this->buildVariableBag($params, $context);

        $this->dispatch(
            'fax',
            new FaxEvent(
                $this->renderTemplate('fax/'.($type ? ($type.'/') : '').$vars->get('template').'.html.twig', $vars),
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
