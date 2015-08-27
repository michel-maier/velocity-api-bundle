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
 * Abstract Sms Event Action.
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
abstract class AbstractSmsEventAction
{
    use ServiceTrait;
    use ArrayizerTrait;
    use TemplatingAwareTrait;
    use TranslatorAwareTrait;
    /**
     * @param array $sms
     *
     * @return $this
     */
    protected function renderAndSend($sms)
    {
        return $this->send($this->render($sms));
    }
    /**
     * @param array $sms
     *
     * @return $this
     */
    abstract protected function send($sms);
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
        return 'sms/'.$template.'.html.twig';
    }
    /**
     * @param array $sms
     *
     * @return array
     */
    protected function render($sms)
    {
        $sms['content'] = $this->getTemplating()->render($this->getTemplatePath($sms['template']), $sms['data']);

        unset($sms['template']);
        unset($sms['data']);

        return $sms;
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

        $data = $this->arrayize($subject) + $arguments + $params;

        return [
            'template'    => str_replace('.', '/', $eventName),
            'data'        => $this->buildData($data),
            'recipients'  => $this->buildRecipients($data),
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

        if (isset($data['phone'])) {
            return [
                $data['phone'] => (isset($data['firstName']) && isset($data['lastName'])) ? sprintf('%s %s', $data['firstName'], $data['lastName']) : $data['phone'],
            ];
        }

        return [];
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