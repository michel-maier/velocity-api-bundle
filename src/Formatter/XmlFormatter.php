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

use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Velocity\Bundle\ApiBundle\Traits\ServiceTrait;
use Velocity\Bundle\ApiBundle\Annotation as Velocity;
use Velocity\Bundle\ApiBundle\Traits\SerializerAwareTrait;

/**
 * Xml Formatter Action.
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
class XmlFormatter
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
     * @Velocity\Formatter("text/xml")
     */
    public function format($data, array $options = [])
    {
        $context = SerializationContext::create();

        if (isset($options['groups'])) {
            $context->setGroups($options['groups']);
        }

        return $this->getSerializer()->serialize($data, 'xml', $context);
    }
}
