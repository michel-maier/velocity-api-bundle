<?php

/*
 * This file is part of the VELOCITY package.
 *
 * (c) PHPPRO <opensource@phppro.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Velocity\Bundle\ApiBundle\Traits\ServiceAwareController;

use Velocity\Bundle\ApiBundle\Service\SupervisionService;

/**
 * SupervisionServiceAwareController trait.
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
trait SupervisionServiceAwareControllerTrait
{
    /**
     * Gets a container service by its id.
     *
     * @param string $id The service id
     *
     * @return object The service
     */
    public abstract function get($id);
    /**
     * @return SupervisionService
     */
    protected function getSupervisionService()
    {
        return $this->get('velocity.supervision');
    }
}