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

use JMS\Serializer\SerializerInterface;

/**
 * SerializerAwareController trait.
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
trait SerializerAwareControllerTrait
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
     * @return SerializerInterface
     */
    protected function getSerializer()
    {
        return $this->get('jms_serializer');
    }
}
