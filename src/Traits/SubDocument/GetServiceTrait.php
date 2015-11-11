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
     * @param mixed  $parentId
     * @param mixed  $id
     * @param string $property
     * @param array  $options
     *
     * @return mixed
     */
    public function getProperty($parentId, $id, $property, $options = [])
    {
        return $this->getRepository()->getProperty($parentId, $this->getRepoKey([$id, $property]), $options);
    }
    /**
     * Return the property of the specified document if exist or default value otherwise.
     *
     * @param mixed  $parentId
     * @param mixed  $id
     * @param string $property
     * @param mixed  $defaultValue
     * @param array  $options
     *
     * @return mixed
     */
    public function getPropertyIfExist($parentId, $id, $property, $defaultValue = null, $options = [])
    {
        return $this->getRepository()->getPropertyIfExist($parentId, $this->getRepoKey([$id, $property]), $defaultValue, $options);
    }
    /**
     * Test if specified document exist.
     *
     * @param string $parentId
     * @param mixed  $id
     * @param array  $options
     *
     * @return bool
     */
    public function has($parentId, $id, $options = [])
    {
        return $this->getRepository()->hasProperty($parentId, $this->getRepoKey([$id]), $options);
    }
    /**
     * Test if specified document exist by specified field.
     *
     * @param string $parentId
     * @param string $fieldName
     * @param mixed  $fieldValue
     * @param array  $options
     *
     * @return bool
     */
    public function hasBy($parentId, $fieldName, $fieldValue, $options = [])
    {
        return 0 < count($this->find($parentId, [$fieldName => $fieldValue], 1, 0, $options));
    }
    /**
     * Test if specified document does not exist.
     *
     * @param string $parentId
     * @param mixed  $id
     * @param array  $options
     *
     * @return bool
     */
    public function hasNot($parentId, $id, $options = [])
    {
        return !$this->has($parentId, $id, $options);
    }
    /**
     * Return the specified document.
     *
     * @param string $parentId
     * @param mixed  $id
     * @param array  $fields
     * @param array  $options
     *
     * @return mixed
     */
    public function get($parentId, $id, $fields = [], $options = [])
    {
        return $this->callback(
            $parentId,
            'fetched',
            $this->convertToModel(
                $this->getRepository()->getHashProperty($parentId, $this->getRepoKey([$id], $options), $fields, $options),
                $options
            ),
            $options
        );
    }
    /**
     * Check if specified document exist.
     *
     * @param string $parentId
     * @param mixed  $id
     * @param array  $options
     *
     * @return $this
     *
     * @throws \Exception
     */
    public function checkExist($parentId, $id, $options = [])
    {
        $this->getRepository()->checkPropertyExist($parentId, $this->getRepoKey([$id], $options), $options);

        return $this;
    }
    /**
     * Check is specified document does not exist.
     *
     * @param string $parentId
     * @param mixed  $id
     * @param array  $options
     *
     * @return $this
     *
     * @throws \Exception
     */
    public function checkNotExist($parentId, $id, $options = [])
    {
        $this->getRepository()->checkPropertyNotExist($parentId, $this->getRepoKey([$id], $options), $options);

        return $this;
    }
    /**
     * @return RepositoryInterface
     */
    public abstract function getRepository();
    /**
     * Retrieve the documents matching the specified criteria.
     *
     * @param string   $parentId
     * @param array    $criteria
     * @param array    $fields
     * @param null|int $limit
     * @param int      $offset
     * @param array    $sorts
     * @param array    $options
     *
     * @return mixed
     */
    public abstract function find($parentId, $criteria = [], $fields = [], $limit = null, $offset = 0, $sorts = [], $options = []);
    /**
     * Execute the registered callback and return the updated subject.
     *
     * @param mixed  $parentId
     * @param string $key
     * @param mixed  $subject
     * @param array  $options
     *
     * @return mixed
     */
    protected abstract function callback($parentId, $key, $subject = null, $options = []);
    /**
     * Convert provided data (array) to a model.
     *
     * @param array $data
     * @param array $options
     *
     * @return mixed
     */
    protected abstract function convertToModel(array $data, $options = []);
    /**
     * @param array $ids
     * @param array $options
     *
     * @return string
     */
    public abstract function getRepoKey(array $ids = [], $options = []);
}
