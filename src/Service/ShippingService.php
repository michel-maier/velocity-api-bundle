<?php

/*
 * This file is part of the VELOCITY package.
 *
 * (c) PHPPRO <opensource@phppro.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Velocity\Bundle\ApiBundle\Service;

use Velocity\Bundle\ApiBundle\Traits\ServiceTrait;
use Velocity\Bundle\ApiBundle\Traits\ServiceAware;

/**
 * Shipping Service.
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
class ShippingService
{
    use ServiceTrait;
    use ServiceAware\DateServiceAwareTrait;
    /**
     * @param DateService $dateService
     */
    public function __construct(DateService $dateService)
    {
        $this->setDateService($dateService);
    }
    /**
     * @param \DateTime $now
     * @param array     $options
     *
     * @return \DateTime[]
     */
    public function computeDelayInterval(\DateTime $now, $options = [])
    {
        $options += [
            'minDelayInDays' => 1,
            'maxDelayInDays' => 1,
            'businessDays'   => true,
            'holidays'       => [],
        ];

        return $this->getDateService()->computeIntervalInDays(
            $this->getDateService()->shiftDateOutsideHolidays($now, $options['holidays']),
            $options['minDelayInDays'],
            $options['maxDelayInDays'],
            true === $options['businessDays']
        );
    }
}
