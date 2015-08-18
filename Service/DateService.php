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

/**
 * Date Service.
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
class DateService
{
    use ServiceTrait;
    /**
     * @param \DateTime $now
     * @param int       $minDays
     * @param int       $maxDays
     * @param bool      $businessDaysOnly
     *
     * @return array
     */
    public function computeIntervalInDays(\DateTime $now, $minDays, $maxDays, $businessDaysOnly = false)
    {
        return [
            $this->computeDateInFuture($now, $minDays, $businessDaysOnly),
            $this->computeDateInFuture($now, $maxDays, $businessDaysOnly),
        ];
    }
    /**
     * @param \DateTime $now
     * @param int       $days
     * @param bool      $businessDaysOnly
     *
     * @return \DateTime
     */
    public function computeDateInFuture(\DateTime $now, $days, $businessDaysOnly = false)
    {
        $date = (clone $now);

        if (true !== $businessDaysOnly) {
            return $date->add(new \DateInterval(sprintf('P%dD', $days)));
        }

        for ($i = 0; $i < $days; $i++) {
            switch ((int)$date->format('N')) {
                case 5: // friday
                case 6: // saturday
                    $offset = 3;
                    break;
                case 7: // sunday
                    $offset = 2;
                    break;
                case 1: // monday
                case 2: // tuesday
                case 3: // wednesday
                case 4: // thursday
                default:
                    $offset = 1;
                    break;
            }
            $date->add(new \DateInterval(sprintf('P%dD', $offset)));
        }

        return $date;
    }
    /**
     * @param \DateTime $date
     * @param $holidays
     *
     * @return \DateTime
     */
    public function shiftDateOutsideHolidays(\DateTime $date, $holidays)
    {

        foreach($holidays as $holiday) {
            $start = new \DateTime($holiday[0]);
            $end = new \DateTime($holiday[1]);
            if ($date >= $start && $date < $end) {
                $date = $end;
            }
        }

        return $date;
    }
}