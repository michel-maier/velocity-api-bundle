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

use Exception;

/**
 * Document Service Interface.
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
interface DocumentServiceInterface
{
    /**
     * Return the document type.
     *
     * @return string
     */
    public function getTypes();
    /**
     * Retrieve the documents matching the specified criteria.
     *
     * @param array    $criteria
     * @param array    $fields
     * @param null|int $limit
     * @param int      $offset
     * @param array    $sorts
     * @param array    $options
     *
     * @return mixed
     */
    public function find(
        $criteria = [],
        $fields = [],
        $limit = null,
        $offset = 0,
        $sorts = [],
        $options = []
    );
    /**
     * Retrieve the documents matching the specified criteria and return a page with total count.
     *
     * @param array    $criteria
     * @param array    $fields
     * @param null|int $limit
     * @param int      $offset
     * @param array    $sorts
     * @param array    $options
     *
     * @return mixed
     */
    public function findWithTotal(
        $criteria = [],
        $fields = [],
        $limit = null,
        $offset = 0,
        $sorts = [],
        $options = []
    );
    /**
     * Return the specified document.
     *
     * @param mixed $id
     * @param array $fields
     * @param array $options
     *
     * @return mixed
     */
    public function get($id, $fields = [], $options = []);
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
    public function getBy($fieldName, $fieldValue, $fields = [], $options = []);
    /**
     * Return the property of the specified document.
     *
     * @param string $fieldName
     * @param mixed  $fieldValue
     * @param string $property
     * @param array  $options
     *
     * @return mixed
     */
    public function getPropertyBy($fieldName, $fieldValue, $property, $options = []);
    /**
     * Return a random document matching the specified criteria.
     *
     * @param array $fields
     * @param array $criteria
     * @param array $options
     *
     * @return mixed
     */
    public function getRandom($fields = [], $criteria = [], $options = []);
    /**
     * Return the list of the specified documents.
     *
     * @param array $ids
     * @param array $fields
     * @param array $options
     *
     * @return mixed
     */
    public function getBulk($ids, $fields = [], $options = []);
    /**
     * Create a new document.
     *
     * @param mixed $data
     * @param array $options
     *
     * @return mixed
     */
    public function create($data, $options = []);
    /**
     * Create document if not exist or update it.
     *
     * @param mixed $data
     * @param array $options
     *
     * @return mixed
     */
    public function createOrUpdate($data, $options = []);
    /**
     * Create a list of documents.
     *
     * @param mixed $bulkData
     * @param array $options
     *
     * @return mixed
     */
    public function createBulk($bulkData, $options = []);
    /**
     * Create documents if not exist or update them.
     *
     * @param mixed $bulkData
     * @param array $options
     *
     * @return mixed
     */
    public function createOrUpdateBulk($bulkData, $options = []);
    /**
     * Create documents if not exist or delete them.
     *
     * @param mixed $bulkData
     * @param array $options
     *
     * @return mixed
     */
    public function createOrDeleteBulk($bulkData, $options = []);
    /**
     * Count documents matching the specified criteria.
     *
     * @param mixed $criteria
     * @param array $options
     *
     * @return mixed
     */
    public function count($criteria = [], $options = []);
    /**
     * Update the specified document.
     *
     * @param mixed $id
     * @param mixed $data
     * @param array $options
     *
     * @return mixed
     */
    public function update($id, $data, $options = []);
    /**
     * Delete the specified document.
     *
     * @param mixed $id
     * @param array $options
     *
     * @return mixed
     */
    public function delete($id, $options = []);
    /**
     * Delete the specified documents.
     *
     * @param array $ids
     * @param array $options
     *
     * @return mixed
     */
    public function deleteBulk($ids, $options = []);
    /**
     * Purge all the documents matching the specified criteria.
     *
     * @param array $criteria
     * @param array $options
     *
     * @return mixed
     */
    public function purge($criteria = [], $options = []);
    /**
     * Return the property of the specified document.
     *
     * @param mixed  $id
     * @param string $property
     * @param array  $options
     *
     * @return mixed
     */
    public function getProperty($id, $property, $options = []);
    /**
     * Test if specified document exist.
     *
     * @param mixed $id
     * @param array $options
     *
     * @return bool
     */
    public function has($id, $options = []);
    /**
     * Test if specified document does not exist.
     *
     * @param mixed $id
     * @param array $options
     *
     * @return bool
     */
    public function hasNot($id, $options = []);
    /**
     * Check if specified document exist.
     *
     * @param mixed $id
     * @param array $options
     *
     * @return $this
     *
     * @throws Exception
     */
    public function checkExist($id, $options = []);
    /**
     * Check if specified document exist by specified field and value.
     *
     * @param string $field
     * @param mixed  $value
     * @param array  $options
     *
     * @return $this
     *
     * @throws \Exception
     */
    public function checkExistBy($field, $value, $options = []);
    /**
     * Check if specified document exist by specified field and values.
     *
     * @param string $field
     * @param array  $values
     * @param array  $options
     *
     * @return $this
     *
     * @throws \Exception
     */
    public function checkExistByBulk($field, array $values, $options = []);
    /**
     * Check is specified document does not exist.
     *
     * @param mixed $id
     * @param array $options
     *
     * @return $this
     *
     * @throws Exception
     */
    public function checkNotExist($id, $options = []);
    /**
     * Increment the specified property of the specified document.
     *
     * @param mixed        $id
     * @param string|array $property
     * @param int          $value
     * @param array        $options
     *
     * @return $this
     */
    public function increment($id, $property, $value = 1, $options = []);
    /**
     * Decrement the specified property of the specified document.
     *
     * @param mixed        $id
     * @param string|array $property
     * @param int          $value
     * @param array        $options
     *
     * @return $this
     */
    public function decrement($id, $property, $value = 1, $options = []);
    /**
     * Replace all the specified documents.
     *
     * @param array $data
     * @param array $options
     *
     * @return mixed
     */
    public function replaceBulk($data, $options = []);
}
