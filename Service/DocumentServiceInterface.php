<?php

namespace Velocity\Bundle\ApiBundle\Service;

use Exception;

interface DocumentServiceInterface
{
    /**
     * @return string
     */
    public function getType();
    /**
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
        $criteria = [], $fields = [], $limit = null, $offset = 0, $sorts = [], $options = []
    );
    /**
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
        $criteria = [], $fields = [], $limit = null, $offset = 0, $sorts = [], $options = []
    );
    /**
     * @param mixed $id
     * @param array $fields
     * @param array $options
     *
     * @return mixed
     */
    public function get($id, $fields = [], $options = []);
    /**
     * @param string $fieldName
     * @param mixed  $fieldValue
     * @param array  $fields
     * @param array  $options
     *
     * @return mixed
     */
    public function getBy($fieldName, $fieldValue, $fields = [], $options = []);
    /**
     * @param array $fields
     * @param array $criteria
     * @param array $options
     *
     * @return mixed
     */
    public function getRandom($fields = [], $criteria = [], $options = []);
    /**
     * @param array  $ids
     * @param array  $fields
     * @param array  $options
     *
     * @return mixed
     */
    public function getBulk($ids, $fields = [], $options = []);
    /**
     * @param mixed $data
     * @param array $options
     *
     * @return mixed
     */
    public function create($data, $options = []);
    /**
     * @param mixed $data
     * @param array $options
     *
     * @return mixed
     */
    public function createOrUpdate($data, $options = []);
    /**
     * @param mixed $bulkData
     * @param array $options
     *
     * @return mixed
     */
    public function createBulk($bulkData, $options = []);
    /**
     * @param mixed $bulkData
     * @param array $options
     *
     * @return mixed
     */
    public function createOrUpdateBulk($bulkData, $options = []);
    /**
     * @param mixed $bulkData
     * @param array $options
     *
     * @return mixed
     */
    public function createOrDeleteBulk($bulkData, $options = []);
    /**
     * @param mixed $criteria
     *
     * @return mixed
     */
    public function count($criteria = []);
    /**
     * @param mixed $id
     * @param mixed $data
     * @param array $options
     *
     * @return mixed
     */
    public function update($id, $data, $options = []);
    /**
     * @param mixed $id
     * @param array $options
     *
     * @return mixed
     */
    public function delete($id, $options = []);
    /**
     * @param array $ids
     * @param array $options
     *
     * @return mixed
     */
    public function deleteBulk($ids, $options = []);
    /**
     * @param array $criteria
     * @param array $options
     *
     * @return mixed
     */
    public function purge($criteria = [], $options = []);
    /**
     * @param mixed  $id
     * @param string $property
     *
     * @return mixed
     */
    public function getProperty($id, $property);
    /**
     * @param mixed $id
     *
     * @return bool
     */
    public function has($id);
    /**
     * @param mixed $id
     *
     * @return bool
     */
    public function hasNot($id);
    /**
     * @param mixed $id
     *
     * @return $this
     *
     * @throws Exception
     */
    public function checkExist($id);
    /**
     * @param mixed $id
     *
     * @return $this
     *
     * @throws Exception
     */
    public function checkNotExist($id);
    /**
     * @param mixed        $id
     * @param string|array $property
     * @param int          $value
     *
     * @return $this
     */
    public function increment($id, $property, $value = 1);
    /**
     * @param mixed        $id
     * @param string|array $property
     * @param int          $value
     *
     * @return $this
     */
    public function decrement($id, $property, $value = 1);
    /**
     * @param array $data
     * @param array $options
     *
     * @return mixed
     */
    public function replaceBulk($data, $options = []);
}