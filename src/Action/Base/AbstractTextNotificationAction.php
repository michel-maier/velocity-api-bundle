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
use Velocity\Core\Traits\ParameterAware;
use Velocity\Core\Traits\TemplatingAwareTrait;
use Velocity\Core\Traits\TranslatorAwareTrait;
use Symfony\Component\Templating\EngineInterface;
use Velocity\Bundle\ApiBundle\Traits\ServiceAware;
use Velocity\Bundle\ApiBundle\Annotation as Velocity;
use Symfony\Component\Translation\TranslatorInterface;
use Velocity\Bundle\ApiBundle\Service\AttachmentService;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
abstract class AbstractTextNotificationAction extends AbstractAction
{
    use TemplatingAwareTrait;
    use TranslatorAwareTrait;
    use ServiceAware\AttachmentServiceAwareTrait;
    use ParameterAware\EnvironmentParameterAwareTrait;
    /**
     * @param EngineInterface          $templating
     * @param TranslatorInterface      $translator
     * @param AttachmentService        $attachmentService
     * @param EventDispatcherInterface $eventDispatcher
     * @param array                    $defaultSenders
     * @param array                    $defaultRecipients
     * @param string                   $env
     */
    public function __construct(EngineInterface $templating, TranslatorInterface $translator, AttachmentService $attachmentService, EventDispatcherInterface $eventDispatcher, array $defaultSenders, array $defaultRecipients, $env)
    {
        $this->setTemplating($templating);
        $this->setTranslator($translator);
        $this->setAttachmentService($attachmentService);
        $this->setEventDispatcher($eventDispatcher);
        $this->setDefaultSenders($defaultSenders);
        $this->setDefaultRecipients($defaultRecipients);
        $this->setEnvironment($env);
    }
    /**
     * @param array $defaultSenders
     *
     * @return $this
     */
    public function setDefaultSenders(array $defaultSenders)
    {
        return $this->setParameter('defaultSenders', $defaultSenders);
    }
    /**
     * @return array
     *
     * @throws \Exception
     */
    public function getDefaultSenders()
    {
        return $this->getParameter('defaultSenders');
    }
    /**
     * @param array $defaultRecipients
     *
     * @return $this
     */
    public function setDefaultRecipients(array $defaultRecipients)
    {
        return $this->setParameter('defaultRecipients', $defaultRecipients);
    }
    /**
     * @return array
     *
     * @throws \Exception
     */
    public function getDefaultRecipients()
    {
        return $this->getParameter('defaultRecipients');
    }
    /**
     * @param string $type
     * @param string $nature
     *
     * @return array
     */
    public function getDefaultSenderByTypeAndNature($type, $nature)
    {
        $senders = $this->filterByEnvAndType(
            $this->getArrayParameterListKey('defaultSenders', $type),
            $this->getEnvironment(),
            $nature
        );

        if (!count($senders)) {
            return null;
        }

        $sender = array_shift($senders);

        return [$sender['sender'] => $sender['name']];
    }
    /**
     * @param string $type
     * @param string $nature
     *
     * @return array
     */
    public function getDefaultRecipientsByTypeAndNature($type, $nature)
    {
        $recipients = $this->filterByEnvAndType(
            $this->getArrayParameterListKey('defaultRecipients', $type),
            $this->getEnvironment(),
            $nature
        );

        if (!count($recipients)) {
            return [];
        }

        $cleanedRecipients = [];

        foreach ($recipients as $email => $recipient) {
            $recipient += ['email' => $email, 'name' => $email];
            $cleanedRecipients[$recipient['email']] = $recipient['name'];
        }

        return $cleanedRecipients;
    }
    /**
     * @param array  $items
     * @param string $env
     * @param string $type
     *
     * @return array
     */
    protected function filterByEnvAndType(array $items, $env, $type)
    {
        foreach ($items as $k => $item) {
            if (isset($item['envs']) && is_array($item['envs']) && count($item['envs'])) {
                if (!in_array('*', $item['envs']) && !in_array($env, $item['envs'])) {
                    unset($items[$k]);
                    continue;
                }
            }
            if (isset($item['types']) && is_array($item['types']) && count($item['types'])) {
                if (!in_array('*', $item['types']) && !in_array($type, $item['types'])) {
                    unset($items[$k]);
                    continue;
                }
            }
        }

        return $items;
    }
    /**
     * @param Bag $params
     * @param Bag $context
     *
     * @return Bag
     */
    protected function buildVariableBag(Bag $params, Bag $context)
    {
        return new Bag(['env' => $this->getEnvironment()] + $params->all() + $context->all());
    }
    /**
     * @param string $name
     * @param Bag    $vars
     *
     * @return string
     */
    protected function renderTemplate($name, Bag $vars)
    {
        return $this->getTemplating()->render($name, $vars->all());
    }
    /**
     * @param string $expression
     * @param Bag    $vars
     *
     * @return string
     */
    protected function renderInlineTemplate($expression, Bag $vars)
    {
        return $this->getTemplating()->render(
            'VelocityApiBundle::expression.txt.twig',
            ['_expression' => $expression] + $vars->all()
        );
    }
}
