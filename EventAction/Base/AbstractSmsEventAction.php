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

use Velocity\Bundle\ApiBundle\EventAction\Context;
use Velocity\Bundle\ApiBundle\Traits\ServiceAware;
use Velocity\Bundle\ApiBundle\Traits\ServiceTrait;
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
     * @param Context $context
     *
     * @return array
     */
    protected function buildFromContext(Context $context)
    {
        return [
            'template'    => $context->getVariable('template'),
            'data'        => $this->buildData($context->getVariables()),
            'recipients'  => $this->buildRecipients(
                $context->hasVariable('recipient') ? $context->getVariable('recipient') : $context->getVariable('recipients', [])
            ),
            'sender'      => $this->buildSender($context->getVariable('sender', null)),
            'options'     => $this->buildOptions($context->getVariable('options', [])),
        ];
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
