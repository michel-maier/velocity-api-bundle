<?php

/*
 * This file is part of the VELOCITY package.
 *
 * (c) PHPPRO <opensource@phppro.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Velocity\Bundle\ApiBundle\Traits\Document;

use Velocity\Bundle\ApiBundle\RepositoryInterface;

/**
 * Get service trait.
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
trait GetServiceTrait
{
    /**
     * Return the property of the specified document.
     *
     * @param mixed  $id
     * @param string $property
     * @param array  $options
     *
     * @return mixed
     */
    public function getProperty($id, $property, $options = [])
    {
        return $this->getRepository()->getProperty($id, $property, $options);
    }
    /**
     * Return the property of the specified document if exist or default value otherwise.
     *
     * @param mixed  $id
     * @param string $property
     * @param mixed  $defaultValue
     * @param array  $options
     *
     * @return mixed
     */
    public function getPropertyIfExist($id, $property, $defaultValue = null, $options = [])
    {
        return $this->getRepository()->getPropertyIfExist($id, $property, $defaultValue, $options);
    }
    /**
     * Test if specified document exist.
     *
     * @param mixed $id
     * @param array $options
     *
     * @return bool
     */
    public function has($id, $options = [])
    {
        return $this->getRepository()->has($id, $options);
    }
    /**
     * Test if specified document exist by specified field.
     *
     * @param string $fieldName
     * @param mixed  $fieldValue
     * @param array  $options
     *
     * @return bool
     */
    public function hasBy($fieldName, $fieldValue, $options = [])
    {
        return $this->getRepository()->hasBy($fieldName, $fieldValue, $options);
    }
    /**
     * Test if specified document does not exist.
     *
     * @param mixed $id
     * @param array $options
     *
     * @return bool
     */
    public function hasNot($id, $options = [])
    {
        return $this->getRepository()->hasNot($id, $options);
    }
    /**
     * Return the specified document.
     *
     * @param mixed $id
     * @param array $fields
     * @param array $options
     *
     * @return mixed
     */
    public function get($id, $fields = [], $options = [])
    {
        return $this->callback(
            'fetched',
            $this->convertToModel($this->getRepository()->get($id, $fields, $options), $options),
            $options
        );
    }
    /**
     * Return the list of the specified documents.
     *
     * @param array $ids
     * @param array $fields
     * @param array $options
     *
     * @return mixed
     */
    public function getBulk($ids, $fields = [], $options = [])
    {
        $docs = [];

        foreach ($this->getRepository()->find(['_id' => $ids], $fields, $options) as $k => $v) {
            $docs[$k] = $this->callback('fetched', $this->convertToModel($v, $options), $options);
        }

        return $docs;
    }
    /**
     * Return the specified document by the specified field.
     *
     * @param string $fieldName
     * @param mixed  $fieldValue
     * @param array  $fields
     * @param array  $options
     *
     * @return mixed
     */
    public function getBy($fieldName, $fieldValue, $fields = [], $options = [])
    {
        return $this->callback(
            'fetched',
            $this->convertToModel($this->getRepository()->getBy($fieldName, $fieldValue, $fields, $options)),
            $options
        );
    }
    /**
     * Return a random document matching the specified criteria.
     *
     * @param array $fields
     * @param array $criteria
     * @param array $options
     *
     * @return mixed
     */
    public function getRandom($fields = [], $criteria = [], $options = [])
    {
        return $this->callback(
            'fetched',
            $this->convertToModel($this->getRepository()->getRandom($fields, $criteria, $options)),
            $options
        );
    }
    /**
     * Check if specified document exist.
     *
     * @param mixed $id
     * @param array $options
     *
     * @return $this
     *
     * @throws \Exception
     */
    public function checkExist($id, $options = [])
    {
        $this->getRepository()->checkExist($id, $options);

        return $this;
    }
    /**
     * Check is specified document does not exist.
     *
     * @param mixed $id
     * @param array $options
     *
     * @return $this
     *
     * @throws \Exception
     */
    public function checkNotExist($id, $options = [])
    {
        $this->getRepository()->checkNotExist($id, $options);

        return $this;
    }
    /**
     * @return RepositoryInterface
     */
    public abstract function getRepository();
    /**
     * Execute the registered callback and return the updated subject.
     *
     * @param string $key
     * @param mixed  $subject
     * @param array  $options
     *
     * @return mixed
     */
    protected abstract function callback($key, $subject = null, $options = []);
    /**
     * Convert provided data (array) to a model.
     *
     * @param array $data
     * @param array $options
     *
     * @return mixed
     */
    protected abstract function convertToModel(array $data, $options = []);
}
