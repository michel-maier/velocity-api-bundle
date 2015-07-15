<?php

namespace Velocity\Bundle\ApiBundle\Service;

interface SubDocumentServiceInterface
{
    /**
     * @return string
     */
    public function getType();
    /**
     * @return string
     */
    public function getParentType();
    /**
     * @param mixed    $parentId
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
        $parentId, $criteria = [], $fields = [], $limit = null, $offset = 0, $sorts = [], $options = []
    );
    /**
     * @param mixed $parentId
     * @param mixed $id
     * @param array $fields
     * @param array $options
     *
     * @return mixed
     */
    public function get($parentId, $id, $fields = [], $options = []);
    /**
     * @param mixed $parentId
     * @param mixed $data
     * @param array $options
     *
     * @return mixed
     */
    public function create($parentId, $data, $options = []);
    /**
     * @param mixed $parentId
     * @param mixed $id
     * @param mixed $data
     * @param array $options
     *
     * @return mixed
     */
    public function update($parentId, $id, $data, $options = []);
    /**
     * @param mixed $parentId
     * @param mixed $id
     * @param array $options
     *
     * @return mixed
     */
    public function delete($parentId, $id, $options = []);
    /**
     * @param mixed $parentId
     * @param array $options
     *
     * @return mixed
     */
    public function purge($parentId, $options = []);
}