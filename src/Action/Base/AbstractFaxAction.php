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

use Velocity\Bundle\ApiBundle\Action\Context;
use Velocity\Bundle\ApiBundle\Traits\ServiceAware;
use Velocity\Bundle\ApiBundle\Event as VelocityEvent;
use Velocity\Bundle\ApiBundle\Traits\TemplatingAwareTrait;
use Velocity\Bundle\ApiBundle\Traits\TranslatorAwareTrait;

/**
 * Abstract Fax Event Action.
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
abstract class AbstractFaxAction extends AbstractAction
{
    use TemplatingAwareTrait;
    use TranslatorAwareTrait;
    use ServiceAware\AttachmentServiceAwareTrait;
    /**
     * @param array $fax
     *
     * @return $this
     */
    protected function renderAndSend($fax)
    {
        return $this->send($this->render($fax));
    }
    /**
     * @param array $fax
     *
     * @return $this
     */
    abstract protected function send($fax);
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
        return 'fax/'.$template.'.html.twig';
    }
    /**
     * @param array $fax
     *
     * @return array
     */
    protected function render($fax)
    {
        $fax['content'] = $this->getTemplating()->render($this->getTemplatePath($fax['template']), $fax['data']);
        $fax['subject'] = $this->getTranslator()->trans(str_replace('/', '_', $fax['template']), [], 'fax');

        unset($fax['template']);
        unset($fax['data']);

        return $fax;
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
            'attachments' => $this->buildRecipients(
                $context->hasVariable('attachment') ? $context->getVariable('attachment') : $context->getVariable('attachments', [])
            ),
            'images'      => $this->buildRecipients(
                $context->hasVariable('attachment') ? $context->getVariable('attachment') : $context->getVariable('images', [])
            ),
            'sender'      => $this->buildSender($context->getVariable('sender', [])),
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

        if (isset($data['fax'])) {
            return [
                $data['fax'] => (isset($data['firstName']) && isset($data['lastName'])) ? sprintf('%s %s', $data['firstName'], $data['lastName']) : $data['fax'],
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
