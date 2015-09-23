<?php

/*
 * This file is part of the VELOCITY package.
 *
 * (c) PHPPRO <opensource@phppro.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Velocity\Bundle\ApiBundle\Traits;

use JMS\Serializer\SerializerInterface;

/**
 * SerializerAware trait.
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
trait SerializerAwareTrait
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
     * @param SerializerInterface $service
     *
     * @return $this
     */
    public function setSerializer(SerializerInterface $service)
    {
        return $this->setService('serializer', $service);
    }
    /**
     * @return SerializerInterface
     */
    public function getSerializer()
    {
        return $this->getService('serializer');
    }
}
