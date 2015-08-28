<?php

/*
 * This file is part of the VELOCITY package.
 *
 * (c) PHPPRO <opensource@phppro.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Velocity\Bundle\ApiBundle\Traits\ServiceAware;

use Velocity\Bundle\ApiBundle\Service\InvitationEventService;

/**
 * InvitationEventServiceAware trait.
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
trait InvitationEventServiceAwareTrait
{
    /**
     * @param string $key
     * @param mixed  $service
     *
     * @return $this
     */
    protected abstract function setService($key, $service);
    /**
     * @param string $key
     *
     * @return mixed
     */
    protected abstract function getService($key);
    /**
     * @return InvitationEventService
     */
    public function getInvitationEventService()
    {
        return $this->getService('invitationEvent');
    }
    /**
     * @param InvitationEventService $service
     *
     * @return $this
     */
    public function setInvitationEventService(InvitationEventService $service)
    {
        return $this->setService('invitationEvent', $service);
    }
}
