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

use Velocity\Bundle\ApiBundle\Service\EventService;

/**
 * EventServiceAware trait.
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
trait EventServiceAwareTrait
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
     * @return EventService
     */
    public function getEventService()
    {
        return $this->getService('event');
    }
    /**
     * @param EventService $service
     *
     * @return $this
     */
    public function setEventService(EventService $service)
    {
        return $this->setService('event', $service);
    }
}
