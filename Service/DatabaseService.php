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

use MongoId;
use Exception;
use MongoClient;
use MongoCollection;
use Velocity\Bundle\ApiBundle\Traits\ServiceTrait;

/**
 * Database Service.
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
class DatabaseService
{
    use ServiceTrait;
    /**
     * Constructs a new service
     *
     * @param MongoClient $mongoClient
     * @param string      $databaseName
     * @param bool        $randomDatabaseName
     */
    public function __construct(MongoClient $mongoClient, $databaseName, $randomDatabaseName = false)
    {
        $this->setMongoClient($mongoClient);

        if (true === $randomDatabaseName) {
            $databaseName .= '_'.((int) microtime(true)).'_'.substr(md5(rand(0, 10000)), -8);
        }

        if (64 <= strlen($databaseName)) {
            throw $this->createException(
                500,
                "Database name is too long, maximum is 64 characters (found: %d)",
                strlen($databaseName)
            );
        }
        
        $this->setDatabaseName($databaseName);
    }
    /**
     * Return the underlying database name.
     *
     * @return string
     */
    public function getDatabaseName()
    {
        return $this->getParameter('databaseName');
    }
    /**
     * Set the underlying database name.
     *
     * @param string $databaseName
     *
     * @return $this
     */
    public function setDatabaseName($databaseName)
    {
        return $this->setParameter('databaseName', $databaseName);
    }
    /**
     * Return the underlying Mongo Client (connection).
     *
     * @return MongoClient
     */
    public function getMongoClient()
    {
        return $this->getService('mongoClient');
    }
    /**
     * Set the underlying Mongo Client (connection).
     *
     * @param MongoClient $service
     *
     * @return $this
     */
    public function setMongoClient(MongoClient $service)
    {
        return $this->setService('mongoClient', $service);
    }
    /**
     * Drop the current (or the specified) database.
     *
     * @param string $name
     *
     * @return $this
     */
    public function drop($name = null)
    {
        $this->getMongoClient()
            ->selectDB(null === $name ? $this->getDatabaseName() : $name)
            ->drop()
        ;

        return $this;
    }
    /**
     * Returns the specified Mongo Collection.
     *
     * @param string $name
     * @param array  $options
     *
     * @return MongoCollection
     */
    public function getCollection($name, $options = [])
    {
        return $this->getMongoClient()->selectCollection(
            isset($options['db']) ? $options['db'] : $this->getDatabaseName(),
            $name
        );
    }
    /**
     * Ensure specified id is a MongoId (convert to MongoId if is string).
     *
     * @param string $id
     *
     * @return \MongoId
     *
     * @throws Exception if malformed
     */
    protected function ensureMongoId($id)
    {
        if (is_object($id) && $id instanceof MongoId) {
            return $id;
        }

        if (!preg_match('/^[a-f0-9]{24}$/', $id)) {
            throw $this->createException(412, 'Malformed id');
        }

        return new MongoId($id);
    }
    /**
     * Ensure criteria are well formed (array).
     *
     * @param array $criteria
     *
     * @return array
     */
    protected function buildCriteria($criteria)
    {
        if (!is_array($criteria)) {
            return [];
        }

        if (isset($criteria['_id'])) {
            $criteria['_id'] = $this->ensureMongoId($criteria['_id']);
        }

        if (isset($criteria['$or']) && is_array($criteria['$or'])) {
            foreach ($criteria['$or'] as $a => $b) {
                if (isset($b['_id'])) {
                    $criteria['$or'][$a]['_id'] = $this->ensureMongoId($b['_id']);
                }
            }
        }

        return $criteria;
    }
    /**
     * Ensure document data are well formed (array).
     *
     * @param array $data
     *
     * @return array
     */
    protected function buildData($data)
    {
        if (!is_array($data) || !count($data)) {
            return [];
        }

        if (isset($data['_id'])) {
            $data['_id'] = $this->ensureMongoId($data['_id']);
        }

        return $data;
    }
    /**
     * Ensure documents data are well formed (array).
     *
     * @param array $bulkData
     *
     * @return array
     */
    protected function buildBulkData($bulkData)
    {
        if (!is_array($bulkData) || !count($bulkData)) {
            return [];
        }

        foreach ($bulkData as $a => $b) {
            $bulkData[$a] = $this->buildData($b);
        }

        return $bulkData;
    }
    /**
     * Update (the first matching criteria) a document of the specified collection.
     *
     * @param string $collection
     * @param array  $criteria
     * @param array  $data
     * @param array  $options
     *
     * @return bool
     */
    public function update($collection, $criteria = [], $data = [], $options = [])
    {
        return $this->getCollection($collection, $options)->update(
            $this->buildCriteria($criteria),
            $this->buildData($data),
            $options
        );
    }
    /**
     * Remove (the first matching criteria) a document of the specified collection.
     *
     * @param string $collection
     * @param array  $criteria
     * @param array  $options
     * @return array|bool
     */
    public function remove($collection, $criteria = [], $options = [])
    {
        return $this->getCollection($collection, $options)->remove(
            $this->buildCriteria($criteria),
            $options
        );
    }
    /**
     * Insert a single document into the specified collection.
     *
     * @param string $collection
     * @param array  $data
     * @param array  $options
     * @return array|bool
     */
    public function insert($collection, $data = [], $options = [])
    {
        return $this->getCollection($collection, $options)->insert(
            $this->buildData($data),
            $options
        );
    }
    /**
     * Insert a list of documents into the specified collection.
     *
     * @param string $collection
     * @param array  $bulkData
     * @param array  $options
     *
     * @return mixed
     */
    public function bulkInsert($collection, $bulkData = [], $options = [])
    {
        return $this->getCollection($collection, $options)->batchInsert(
            $this->buildBulkData($bulkData),
            $options
        );
    }
    /**
     * Retrieve (if match criteria) a list of documents from the specified collection.
     *
     * @param string   $collection
     * @param array    $criteria
     * @param array    $fields
     * @param null|int $limit
     * @param int      $offset
     * @param array    $sorts
     * @param array    $options
     *
     * @return \MongoCursor
     */
    public function find(
        $collection,
        $criteria = [],
        $fields = [],
        $limit = null,
        $offset = 0,
        $sorts = [],
        $options = []
    )     {
        $cursor = $this->getCollection($collection, $options)->find(
            $this->buildCriteria($criteria)
        );

        if (is_array($fields)   && count($fields)) {
            $cursor->fields($fields);
        }
        if (is_array($sorts)    && count($sorts)) {
            $cursor->sort($sorts);
        }
        if (is_numeric($offset) && $offset > 0) {
            $cursor->skip($offset);
        }
        if (is_numeric($limit)  && $limit > 0) {
            $cursor->limit($limit);
        }

        return $cursor;
    }
    /**
     * Retrieve (if match criteria) one document from the specified collection.
     *
     * @param string $collection
     * @param array  $criteria
     * @param array  $fields
     * @param array  $options
     *
     * @return array|null
     */
    public function findOne($collection, $criteria = [], $fields = [], $options = [])
    {
        return $this->getCollection($collection, $options)->findOne(
            $this->buildCriteria($criteria),
            $fields
        );
    }
    /**
     * Count the documents matching the criteria.
     *
     * @param string $collection
     * @param array  $criteria
     * @param array  $options
     *
     * @return int
     */
    public function count($collection, $criteria = [], $options = [])
    {
        return $this->find($collection, $criteria, ['_id'], null, 0, [], $options)
            ->count(true);
    }
    /**
     * Ensures the specified index is present on the specified fields of the collection.
     *
     * @param string       $collection
     * @param string|array $fields
     * @param mixed        $index
     * @param array        $options
     *
     * @return bool
     */
    public function ensureIndex($collection, $fields, $index, $options = [])
    {
        return $this->getCollection($collection, $options)->ensureIndex(
            is_array($fields) ? $fields : [$fields => true],
            $index
        );
    }
}
