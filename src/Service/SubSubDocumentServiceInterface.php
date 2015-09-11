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
 * Sub Sub Document Service Interface.
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
interface SubSubDocumentServiceInterface
{
    /**
     * Return the document types.
     *
     * @return array
     */
    public function getTypes();
    /**
     * Retrieve the documents matching the specified criteria.
     *
     * @param mixed    $pParentId
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
        $pParentId,
        $parentId,
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
     * @param mixed    $pParentId
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
    public function findWithTotal(
        $pParentId,
        $parentId,
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
     * @param mixed $pParentId
     * @param mixed $parentId
     * @param mixed $id
     * @param array $fields
     * @param array $options
     *
     * @return mixed
     */
    public function get($pParentId, $parentId, $id, $fields = [], $options = []);
    /**
     * Create a new document.
     *
     * @param mixed $pParentId
     * @param mixed $parentId
     * @param mixed $data
     * @param array $options
     *
     * @return mixed
     */
    public function create($pParentId, $parentId, $data, $options = []);
    /**
     * Create document if not exist or update it.
     *
     * @param mixed $pParentId
     * @param mixed $parentId
     * @param mixed $data
     * @param array $options
     *
     * @return mixed
     */
    public function createOrUpdate($pParentId, $parentId, $data, $options = []);
    /**
     * Create a list of documents.
     *
     * @param mixed $pParentId
     * @param mixed $parentId
     * @param mixed $bulkData
     * @param array $options
     *
     * @return mixed
     */
    public function createBulk($pParentId, $parentId, $bulkData, $options = []);
    /**
     * Create documents if not exist or update them.
     *
     * @param mixed $pParentId
     * @param mixed $parentId
     * @param mixed $bulkData
     * @param array $options
     *
     * @return mixed
     */
    public function createOrUpdateBulk($pParentId, $parentId, $bulkData, $options = []);
    /**
     * Create documents if not exist or delete them.
     *
     * @param mixed $pParentId
     * @param mixed $parentId
     * @param mixed $bulkData
     * @param array $options
     *
     * @return mixed
     */
    public function createOrDeleteBulk($pParentId, $parentId, $bulkData, $options = []);
    /**
     * Count documents matching the specified criteria.
     *
     * @param mixed $pParentId
     * @param mixed $parentId
     * @param mixed $criteria
     *
     * @return mixed
     */
    public function count($pParentId, $parentId, $criteria = []);
    /**
     * Update the specified document.
     *
     * @param mixed $pParentId
     * @param mixed $parentId
     * @param mixed $id
     * @param mixed $data
     * @param array $options
     *
     * @return mixed
     */
    public function update($pParentId, $parentId, $id, $data, $options = []);
    /**
     * Delete the specified document.
     *
     * @param mixed $pParentId
     * @param mixed $parentId
     * @param mixed $id
     * @param array $options
     *
     * @return mixed
     */
    public function delete($pParentId, $parentId, $id, $options = []);
    /**
     * Delete the specified documents.
     *
     * @param mixed $pParentId
     * @param mixed $parentId
     * @param array $ids
     * @param array $options
     *
     * @return mixed
     */
    public function deleteBulk($pParentId, $parentId, $ids, $options = []);
    /**
     * Purge all the documents matching the specified criteria.
     *
     * @param mixed $pParentId
     * @param mixed $parentId
     * @param array $criteria
     * @param array $options
     *
     * @return mixed
     */
    public function purge($pParentId, $parentId, $criteria = [], $options = []);
    /**
     * Return the property of the specified document.
     *
     * @param mixed  $pParentId
     * @param mixed  $parentId
     * @param mixed  $id
     * @param string $property
     * @param array  $options
     *
     * @return mixed
     */
    public function getProperty($pParentId, $parentId, $id, $property, $options = []);
    /**
     * Test if specified document exist.
     *
     * @param mixed $pParentId
     * @param mixed $parentId
     * @param mixed $id
     *
     * @return bool
     */
    public function has($pParentId, $parentId, $id);
    /**
     * Test if specified document does not exist.
     *
     * @param mixed $pParentId
     * @param mixed $parentId
     * @param mixed $id
     *
     * @return bool
     */
    public function hasNot($pParentId, $parentId, $id);
    /**
     * Replace all the specified documents.
     *
     * @param mixed $pParentId
     * @param mixed $parentId
     * @param array $bulkData
     * @param array $options
     *
     * @return mixed
     */
    public function replaceBulk($pParentId, $parentId, $bulkData, $options = []);
    /**
     * Check if specified document exist.
     *
     * @param mixed $pParentId
     * @param mixed $parentId
     * @param mixed $id
     *
     * @return $this
     *
     * @throws Exception
     */
    public function checkExist($pParentId, $parentId, $id);
    /**
     * Check is specified document does not exist.
     *
     * @param mixed $pParentId
     * @param mixed $parentId
     * @param mixed $id
     *
     * @return $this
     *
     * @throws Exception
     */
    public function checkNotExist($pParentId, $parentId, $id);
    /**
     * Increment the specified property of the specified document.
     *
     * @param mixed        $pParentId
     * @param mixed        $parentId
     * @param mixed        $id
     * @param string|array $property
     * @param int          $value
     *
     * @return $this
     */
    public function increment($pParentId, $parentId, $id, $property, $value = 1);
    /**
     * Decrement the specified property of the specified document.
     *
     * @param mixed        $pParentId
     * @param mixed        $parentId
     * @param mixed        $id
     * @param string|array $property
     * @param int          $value
     *
     * @return $this
     */
    public function decrement($pParentId, $parentId, $id, $property, $value = 1);
}
