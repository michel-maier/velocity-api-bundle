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

use Velocity\Bundle\ApiBundle\Service\ActionService;

/**
 * ActionServiceAware trait.
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
trait ActionServiceAwareTrait
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
     * @return ActionService
     */
    public function getActionService()
    {
        return $this->getService('action');
    }
    /**
     * @param ActionService $service
     *
     * @return $this
     */
    public function setActionService(ActionService $service)
    {
        return $this->setService('action', $service);
    }
}
