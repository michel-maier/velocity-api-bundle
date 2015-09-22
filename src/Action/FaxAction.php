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

use Velocity\Bundle\ApiBundle\Bag;
use Velocity\Bundle\ApiBundle\Event\FaxEvent;
use Velocity\Bundle\ApiBundle\Traits\ServiceAware;
use Velocity\Bundle\ApiBundle\Annotation as Velocity;
use Velocity\Bundle\ApiBundle\Action\Base\AbstractTextNotificationAction;

/**
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
class FaxAction extends AbstractTextNotificationAction
{
    /**
     * @param Bag $params
     * @param Bag $context
     *
     * @Velocity\Action("fax", description="send a fax")
     */
    public function sendFax(Bag $params, Bag $context)
    {
        $this->sendFaxByType(null, $params, $context);
    }
    /**
     * @param Bag $params
     * @param Bag $context
     *
     * @Velocity\Action("fax_user", description="send a user fax")
     */
    public function sendUserFax(Bag $params, Bag $context)
    {
        $params->setDefault('sender', $this->getDefaultSenderByTypeAndNature('fax_user', $params->get('template')));
        $this->sendFaxByType('user', $params, $context);
    }
    /**
     * @param Bag $params
     * @param Bag $context
     *
     * @Velocity\Action("fax_admin", description="send an admin fax")
     */
    public function sendAdminFax(Bag $params, Bag $context)
    {
        $params->setDefault('recipients', $this->getDefaultRecipientsByTypeAndNature('fax_admins', $params->get('template')));
        $params->setDefault('sender', $this->getDefaultSenderByTypeAndNature('fax_admin', $params->get('template')));
        $this->sendFaxByType('admin', $params, $context);
    }
    /**
     * @param string $type
     * @param Bag    $params
     * @param Bag    $context
     *
     * @throws \Exception
     */
    protected function sendFaxByType($type, Bag $params, Bag $context)
    {
        $vars = $this->buildVariableBag($params, $context);

        $this->dispatch(
            'fax',
            new FaxEvent(
                $this->renderTemplate('fax/'.($type ? ($type.'/') : '').$vars->get('template').'.html.twig', $vars),
                $params->get('recipients'),
                array_map(function ($attachment) use ($vars) {
                    return $this->getAttachmentService()->build($attachment, $vars);
                }, $params->get('attachments', [])),
                $params->get('images', []),
                $params->get('sender', null),
                $params->get('options', [])
            )
        );
    }
}
