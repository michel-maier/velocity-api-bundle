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
 * Math Service.
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
class MathService
{
    use ServiceTrait;
    /**
     * Return computed stats for the specified population.
     *
     * @param array $values
     *
     * @return array
     */
    public function stats($values)
    {
        return [
            'min'          => $this->min($values),
            'max'          => $this->max($values),
            'count'        => $this->count($values),
            'sum'          => $this->sum($values),
            'median'       => $this->median($values),
            'average'      => $this->average($values),
            'percentile90' => $this->percentile(0.9, $values),
        ];
    }
    /**
     * Return sum for the specified population.
     *
     * @param array $values
     *
     * @return number
     */
    public function sum($values)
    {
        return array_sum($values);
    }
    /**
     * Return min for the specified population.
     *
     * @param array $values
     *
     * @return number
     */
    public function min($values)
    {
        return min($values);
    }
    /**
     * Return max for the specified population.
     *
     * @param array $values
     *
     * @return number
     */
    public function max($values)
    {
        return max($values);
    }
    /**
     * Return count for the specified population.
     *
     * @param array $values
     *
     * @return int
     */
    public function count($values)
    {
        return count($values);
    }
    /**
     * Return median for the specified population.
     *
     * @param array $values
     *
     * @return number
     */
    public function median($values)
    {
        return $this->percentile(0.5, $values);
    }
    /**
     * Return average for the specified population.
     *
     * @param array $values
     *
     * @return float
     */
    public function average($values)
    {
        return $this->sum($values) / $this->count($values);
    }
    /**
     * Return specified percentile for the specified population.
     *
     * @param float $rank
     * @param array $population
     * @param null $field
     *
     * @return number
     */
    public function percentile($rank, $population, $field = null)
    {
        if (0 < $rank && $rank < 1) {
            $p = $rank;
        }elseif (1 < $rank && $rank <= 100) {
            $p = $rank * .01;
        }else {
            throw $this->createException('math.percentile.malformed', $rank);
        }

        if (0 === count($population)){
            return 0;
        }

        if (null === $field) {
            $data = $population;
        }else{
            $data = array();
            foreach($population as $item) {
                if (false === isset($item[$field])) {
                    throw $this->createException(
                        'math.population.field.unknown',
                        $field,
                        $item
                    );
                }
                $data[] = $item[$field];
            }
        }
        $count       = count($data);
        $allindex    = ($count - 1) * $p;
        $intvalindex = intval($allindex);
        $floatval    = $allindex - $intvalindex;
        sort($data);

        if(false === is_float($floatval)){
            $result = $data[$intvalindex];
        }else {
            if($count > $intvalindex+1) {
                $result = $floatval
                    * ($data[$intvalindex + 1] - $data[$intvalindex])
                    + $data[$intvalindex];
            } else {
                $result = $data[$intvalindex];
            }
        }
        return $result;
    }
}