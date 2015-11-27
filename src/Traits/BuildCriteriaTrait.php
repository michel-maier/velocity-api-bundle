<?php

namespace Velocity\Bundle\ApiBundle\Traits;

/**
 * Class BuildCriteriaTrait
 */
trait BuildCriteriaTrait
{
    /**
     * Cast criteria values and restore value keys
     *
     * @param array $criteria
     *
     * @return array
     */
    protected function prepareCriteria(array $criteria = [])
    {
        foreach ($criteria as $criteriaKey => &$criteriaValue) {
            if (false !== strpos($criteriaKey, ':')) {
                unset($criteria[$criteriaKey]);
                $this->prepareCompositeCriteria($criteriaKey, $criteriaValue);
                $criteria[$criteriaKey] = $criteriaValue;
            }
        }

        return $criteria;
    }

    /**
     * Transform composite criteria to normal criteria
     *
     * A composite criteria use this pattern 'key:type' => 'value'
     *
     * @param $key
     * @param $value
     */
    protected function prepareCompositeCriteria(&$key, &$value)
    {
        list($key, $criteriaValueType) = explode(':', $key, 2);

        switch (trim($criteriaValueType)) {
            case 'int':
                $value = (int)$value;
                break;
            case 'string':
                $value = (string)$value;
                break;
            case 'bool':
                $value = (bool)$value;
                break;
            case 'array':
                $value = json_decode($value, true);
                break;
            case 'float':
                $value = (double)$value;
                break;
            default:
                break;
        }
    }
}
