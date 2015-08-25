<?php

/*
 * This file is part of the VELOCITY package.
 *
 * (c) PHPPRO <opensource@phppro.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Velocity\Bundle\ApiBundle\DependencyInjection;

use Symfony\Component\EventDispatcher\Event;
use Velocity\Bundle\ApiBundle\Traits\ServiceTrait;
use Velocity\Bundle\ApiBundle\Traits\TranslatorAwareTrait;

/**
 * Abstract Mail Event Action.
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
abstract class AbstractMailEventAction
{
    use ServiceTrait;
    use TemplatingAwareTrait;
    use TranslatorAwareTrait;
    use ServiceAware\AttachmentServiceAwareTrait;
    /**
     * @param array $mail
     *
     * @return $this
     */
    protected function renderAndSend($mail)
    {
        return $this->send($this->render($mail));
    }
    /**
     * @param array $mail
     *
     * @return $this
     */
    protected abstract function send($mail);
    /**
     * @return array
     */
    protected function getStaticData()
    {
        return [];
    }
    /**
     * @param array $mail
     *
     * @return array
     */
    protected function render($mail)
    {
        $mail['content'] = $this->getTemplating()->render($mail['template'].'.html.twig', $mail['data']);
        $mail['subject'] = $this->getTranslator()->trans(str_replace('/', '_', $mail['template']), 'mail');

        unset($mail['template']);
        unset($mail['data']);

        return $mail;
    }
    /**
     * @param Event  $event
     * @param string $eventName
     * @param array  $params
     *
     * @return array
     */
    protected function buildFromEvent(Event $event, $eventName, array $params)
    {
        return [
            'template'    => str_replace('.', '/', $eventName),
            'data'        => $this->buildDataFromEvent($event, $eventName, $params),
            'recipients'  => $this->buildRecipientsFromEvent($event, $eventName, $params),
            'attachments' => $this->buildAttachmentsFromEvent($event, $eventName, $params),
            'images'      => $this->buildImagesFromEvent($event, $eventName, $params),
            'sender'      => $this->buildSenderFromEvent($event, $eventName, $params),
            'options'     => $this->buildOptionsFromEvent($event, $eventName, $params),
        ];
    }
    /**
     * @param Event  $event
     * @param string $eventName
     * @param array  $params
     *
     * @return array
     */
    protected function buildDataFromEvent(Event $event, $eventName, array $params)
    {
        $data = $this->getStaticData();

        $data += (method_exists($event, 'getSubject') ? (array) $event->getSubject() : []);

        return $data;
    }
    /**
     * @param Event  $event
     * @param string $eventName
     * @param array  $params
     *
     * @return array
     */
    protected function buildRecipientsFromEvent(Event $event, $eventName, array $params)
    {
        return isset($params['recipients']) ? $params['recipients'] : [];
    }
    /**
     * @param Event  $event
     * @param string $eventName
     * @param array  $params
     *
     * @return array
     */
    protected function buildAttachmentsFromEvent(Event $event, $eventName, array $params)
    {
        $attachments = [];

        foreach(isset($params['attachments']) ? $params['attachments'] : [] as $attachment) {
            $attachments[] = $this->buildAttachmentFromEvent($attachment, $event, $eventName, $params);
        }

        return $attachments;
    }
    /**
     * @param array  $definition
     * @param Event  $event
     * @param string $eventName
     * @param array  $params
     *
     * @return array
     */
    protected function buildAttachmentFromEvent($definition, Event $event, $eventName, array $params)
    {
        return $this->getAttachmentService()->build(
            $definition,
            $this->buildDataFromEvent($event, $eventName, $params)
        );
    }
    /**
     * @param Event  $event
     * @param string $eventName
     * @param array  $params
     *
     * @return array
     */
    protected function buildImagesFromEvent(Event $event, $eventName, array $params)
    {
        return isset($params['images']) ? $params['images'] : [];
    }
    /**
     * @param Event  $event
     * @param string $eventName
     * @param array  $params
     *
     * @return array
     */
    protected function buildSenderFromEvent(Event $event, $eventName, array $params)
    {
        return isset($params['sender']) ? $params['sender'] : null;
    }
    /**
     * @param Event  $event
     * @param string $eventName
     * @param array  $params
     *
     * @return array
     */
    protected function buildOptionsFromEvent(Event $event, $eventName, array $params)
    {
        return isset($params['options']) ? $params['options'] : [];
    }
}
