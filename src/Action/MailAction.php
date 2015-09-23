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
use Velocity\Bundle\ApiBundle\Event\MailEvent;
use Velocity\Bundle\ApiBundle\Traits\ServiceAware;
use Velocity\Bundle\ApiBundle\Annotation as Velocity;
use Velocity\Bundle\ApiBundle\Action\Base\AbstractTextNotificationAction;

/**
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
class MailAction extends AbstractTextNotificationAction
{
    /**
     * @param Bag $params
     * @param Bag $context
     *
     * @Velocity\Action("mail", description="send a mail")
     */
    public function sendMail(Bag $params, Bag $context)
    {
        $this->sendMailByType(null, $params, $context);
    }
    /**
     * @param Bag $params
     * @param Bag $context
     *
     * @Velocity\Action("mail_user", description="send a user mail")
     */
    public function sendUserMail(Bag $params, Bag $context)
    {
        $params->setDefault('sender', $this->getDefaultSenderByTypeAndNature('mail_user', $params->get('template')));
        $this->sendMailByType('user', $params, $context);
    }
    /**
     * @param Bag $params
     * @param Bag $context
     *
     * @Velocity\Action("mail_admin", description="send an admin mail")
     */
    public function sendAdminMail(Bag $params, Bag $context)
    {
        $params->setDefault('recipients', $this->getDefaultRecipientsByTypeAndNature('mail_admins', $params->get('template')));
        $params->setDefault('sender', $this->getDefaultSenderByTypeAndNature('mail_admin', $params->get('template')));
        $this->sendMailByType('admin', $params, $context);
    }
    /**
     * @param string $type
     * @param Bag    $params
     * @param Bag    $context
     *
     * @throws \Exception
     */
    protected function sendMailByType($type, Bag $params, Bag $context)
    {
        $vars     = $this->buildVariableBag($params, $context);
        $template = ($type ? ($type.'/') : '').$vars->get('template');

        $this->dispatch(
            'mail',
            new MailEvent(
                $this->renderInlineTemplate($this->getTranslator()->trans(str_replace('/', '_', $template), [], 'mail'), $vars),
                $this->renderTemplate('mail/'.$template.'.html.twig', $vars),
                $vars->get('recipients'),
                array_map(function ($attachment) use ($vars) {
                    return $this->getAttachmentService()->build($attachment, $vars);
                }, $params->get('attachments', [])),
                $vars->get('images', []),
                $vars->get('sender', null),
                $vars->get('options', [])
            )
        );
    }
}
