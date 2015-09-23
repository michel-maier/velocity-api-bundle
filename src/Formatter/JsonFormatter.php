<?php

/*
 * This file is part of the VELOCITY package.
 *
 * (c) PHPPRO <opensource@phppro.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Velocity\Bundle\ApiBundle\Formatter;

use JMS\Serializer\SerializerInterface;
use JMS\Serializer\SerializationContext;
use Velocity\Bundle\ApiBundle\Traits\ServiceTrait;
use Velocity\Bundle\ApiBundle\Annotation as Velocity;
use Velocity\Bundle\ApiBundle\Traits\SerializerAwareTrait;

/**
 * Json Formatter Action.
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
class JsonFormatter
{
    use ServiceTrait;
    use SerializerAwareTrait;
    /**
     * @param SerializerInterface $serializer
     */
    public function __construct(SerializerInterface $serializer)
    {
        $this->setSerializer($serializer);
    }
    /**
     * @param mixed $data
     * @param array $options
     *
     * @return string
     *
     * @Velocity\Formatter("application/json")
     * @Velocity\Formatter("text/json")
     */
    public function format($data, array $options = [])
    {
        $context = SerializationContext::create();

        if (isset($options['groups'])) {
            $context->setGroups($options['groups']);
        }

        return $this->getSerializer()->serialize($data, 'json', $context);
    }
}
