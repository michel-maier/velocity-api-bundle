<?php

/*
 * This file is part of the VELOCITY package.
 *
 * (c) PHPPRO <opensource@phppro.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Velocity\Bundle\ApiBundle\Traits\SubDocument;

/**
 * Create by service trait.
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
trait CreateByServiceTrait
{
    /**
     * Create a new document.
     *
     * @param mixed $parentId
     * @param mixed $data
     * @param array $options
     *
     * @return mixed
     */
    public abstract function create($parentId, $data, $options = []);
    /**
     * Create a new document by selecting parent from a specific field.
     *
     * @param string $parentFieldName
     * @param mixed  $parentFieldValue
     * @param mixed  $data
     * @param array  $options
     *
     * @return mixed
     */
    public function createBy($parentFieldName, $parentFieldValue, $data, $options = [])
    {
        return $this->create(
            $this->getParentIdBy($parentFieldName, $parentFieldValue),
            $data,
            $options
        );
    }
    /**
     * Returns the parent id based on the specified field and value to select it.
     *
     * @param string $field
     * @param mixed  $value
     *
     * @return string
     */
    public abstract function getParentIdBy($field, $value);
}
