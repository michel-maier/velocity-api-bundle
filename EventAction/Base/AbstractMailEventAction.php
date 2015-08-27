<?php

/*
 * This file is part of the VELOCITY package.
 *
 * (c) PHPPRO <opensource@phppro.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Velocity\Bundle\ApiBundle\EventAction\Base;

use Symfony\Component\EventDispatcher\Event;
use Velocity\Bundle\ApiBundle\Traits\ServiceAware;
use Velocity\Bundle\ApiBundle\Traits\ServiceTrait;
use Symfony\Component\EventDispatcher\GenericEvent;
use Velocity\Bundle\ApiBundle\Traits\ArrayizerTrait;
use Velocity\Bundle\ApiBundle\Event as VelocityEvent;
use Velocity\Bundle\ApiBundle\Traits\TemplatingAwareTrait;
use Velocity\Bundle\ApiBundle\Traits\TranslatorAwareTrait;

/**
 * Abstract Mail Event Action.
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
abstract class AbstractMailEventAction
{
    use ServiceTrait;
    use ArrayizerTrait;
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
    abstract protected function send($mail);
    /**
     * @return array
     */
    protected function getStaticData()
    {
        return [];
    }
    /**
     * @param string $template
     *
     * @return string
     */
    protected function getTemplatePath($template)
    {
        return 'mail/'.$template.'.html.twig';
    }
    /**
     * @param array $mail
     *
     * @return array
     */
    protected function render($mail)
    {
        $mail['content'] = $this->getTemplating()->render($this->getTemplatePath($mail['template']), $mail['data']);
        $mail['subject'] = $this->getTranslator()->trans(str_replace('/', '_', $mail['template']), [], 'mail');

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
        list($subject, $arguments) = $this->extractEventData($event);

        $data = $this->arrayize($subject, 1) + $arguments + $params;

        return [
            'template'    => str_replace('.', '/', $eventName),
            'data'        => $this->buildData($data),
            'recipients'  => $this->buildRecipients($data),
            'attachments' => $this->buildAttachments($data),
            'images'      => $this->buildImages($data),
            'sender'      => $this->buildSender($data),
            'options'     => $this->buildOptions($data),
        ];
    }
    /**
     * @param Event $event
     *
     * @return array
     */
    protected function extractEventData(Event $event)
    {
        if ($event instanceof VelocityEvent\DocumentEvent) {
            return [$event->getData(), $event->getContext()];
        }

        if ($event instanceof GenericEvent) {
            return [$event->getSubject(), $event->getArguments()];
        }

        return [null, []];
    }
    /**
     * @param array $data
     *
     * @return array
     */
    protected function buildData(array $data = [])
    {
        return $this->getStaticData() + $data;
    }
    /**
     * @param array $data
     *
     * @return array
     */
    protected function buildRecipients(array $data = [])
    {
        if (isset($data['recipients'])) {
            return $data['recipients'];
        }

        if (isset($data['email'])) {
            return [
                $data['email'] => (isset($data['firstName']) && isset($data['lastName'])) ? sprintf('%s %s', $data['firstName'], $data['lastName']) : $data['email'],
            ];
        }

        return [];
    }
    /**
     * @param array $data
     *
     * @return array
     */
    protected function buildAttachments(array $data = [])
    {
        $attachments = [];

        foreach (isset($data['attachments']) ? $data['attachments'] : [] as $attachment) {
            $attachments[] = $this->buildAttachment($attachment, $data);
        }

        return $attachments;
    }
    /**
     * @param array $definition
     * @param array $data
     *
     * @return array
     */
    protected function buildAttachment(array $definition, array $data = [])
    {
        return $this->getAttachmentService()->build($definition, $data);
    }
    /**
     * @param array $data
     *
     * @return array
     */
    protected function buildImages(array $data = [])
    {
        return isset($data['images']) ? $data['images'] : [];
    }
    /**
     * @param array $data
     *
     * @return array
     */
    protected function buildSender(array $data = [])
    {
        return isset($data['sender']) ? $data['sender'] : null;
    }
    /**
     * @param array $data
     *
     * @return array
     */
    protected function buildOptions(array $data = [])
    {
        return isset($data['options']) ? $data['options'] : [];
    }
}
