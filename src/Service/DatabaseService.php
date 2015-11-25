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
use Velocity\Core\Traits\ServiceTrait;
use Velocity\Bundle\ApiBundle\Event\DatabaseQueryEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Database Service.
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
class DatabaseService
{
    use ServiceTrait;
    /**
     * @var \DateTime[]
     */
    protected $timers;
    /**
     * Constructs a new service
     *
     * @param MongoClient              $mongoClient
     * @param EventDispatcherInterface $eventDispatcher
     * @param string                   $databaseName
     * @param bool                     $randomDatabaseName
     *
     * @throws \Exception
     */
    public function __construct(MongoClient $mongoClient, EventDispatcherInterface $eventDispatcher, $databaseName, $randomDatabaseName = false)
    {
        $this->timers = [];

        $this->setEventDispatcher($eventDispatcher);
        $this->setMongoClient($mongoClient);

        if (true === $randomDatabaseName) {
            $databaseName .= '_'.((int) microtime(true)).'_'.substr(md5(rand(0, 10000)), -8);
        }

        if (64 <= strlen($databaseName)) {
            throw $this->createMalformedException(
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
     * Update (the first matching criteria) a document of the specified collection.
     *
     * @param string $collection
     * @param array  $criteria
     * @param array  $data
     * @param array  $options
     *
     * @return bool
     *
     * @throws \Exception
     */
    public function update($collection, $criteria = [], $data = [], $options = [])
    {
        $this->start();

        try {
            $builtCriteria = $this->buildCriteria($criteria);
            $builtData     = $this->buildData($data);

            $result = $this->getCollection($collection, $options)->update(
                $builtCriteria,
                $builtData,
                $options
            );

            list ($startDate, $endDate) = $this->stop();
            $this->dispatch('database.query.executed', new DatabaseQueryEvent('update', 'db.'.$collection.'.update('.json_encode($builtCriteria).', '.json_encode($builtData).')', ['collection' => $collection, 'criteria' => $builtCriteria, 'data' => $builtData, 'options' => $options], $startDate, $endDate, $result));

            return $result;
        } catch (\Exception $e) {
            list ($startDate, $endDate) = $this->stop();
            $this->dispatch('database.query.executed', new DatabaseQueryEvent('update', 'db.'.$collection.'.update('.json_encode($criteria).', '.json_encode($data).')', ['rawCriteria' => $criteria, 'rawData' => $data, 'options' => $options], $startDate, $endDate, null, $e));
            throw $e;
        }
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
        $this->start();

        try {
            $builtCriteria = $this->buildCriteria($criteria);

            $result = $this->getCollection($collection, $options)->remove(
                $builtCriteria,
                $options
            );

            list ($startDate, $endDate) = $this->stop();
            $this->dispatch('database.query.executed', new DatabaseQueryEvent('remove', 'db.'.$collection.'.remove('.json_encode($builtCriteria).')', ['collection' => $collection, 'criteria' => $builtCriteria, 'options' => $options], $startDate, $endDate, $result));

            return $result;
        } catch (\Exception $e) {
            list ($startDate, $endDate) = $this->stop();
            $this->dispatch('database.query.executed', new DatabaseQueryEvent('remove', 'db.'.$collection.'.remove('.json_encode($criteria).')', ['rawCriteria' => $criteria, 'options' => $options], $startDate, $endDate, null, $e));
            throw $e;
        }
    }
    /**
     * Insert a single document into the specified collection.
     *
     * @param string $collection
     * @param array  $data
     * @param array  $options
     *
     * @return array|bool
     *
     * @throws \Exception
     */
    public function insert($collection, $data = [], $options = [])
    {
        $this->start();

        try {
            $builtData = $this->buildData($data);

            $result = $this->getCollection($collection, $options)->insert(
                $builtData,
                $options
            );

            list ($startDate, $endDate) = $this->stop();
            $this->dispatch('database.query.executed', new DatabaseQueryEvent('insert', 'db.'.$collection.'.insert('.json_encode($builtData).')', ['data' => $builtData, 'options' => $options], $startDate, $endDate, $result));

            return $result;
        } catch (\Exception $e) {
            list ($startDate, $endDate) = $this->stop();
            $this->dispatch('database.query.executed', new DatabaseQueryEvent('insert', 'db.'.$collection.'.insert('.json_encode($data).')', ['rawData' => $data, 'options' => $options], $startDate, $endDate, null, $e));
            throw $e;
        }
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
        $this->start();

        try {
            $builtData = $this->buildBulkData($bulkData);

            $result = $this->getCollection($collection, $options)->batchInsert(
                $builtData,
                $options
            );

            list ($startDate, $endDate) = $this->stop();
            $this->dispatch('database.query.executed', new DatabaseQueryEvent('bulkInsert', 'db.'.$collection.'.bulkInsert('.json_encode($builtData).')', ['data' => $builtData, 'options' => $options], $startDate, $endDate, $result));

            return $result;
        } catch (\Exception $e) {
            list ($startDate, $endDate) = $this->stop();
            $this->dispatch('database.query.executed', new DatabaseQueryEvent('bulkInsert', 'db.'.$collection.'.bulkIinsert('.json_encode($bulkData).')', ['rawData' => $bulkData, 'options' => $options], $startDate, $endDate, null, $e));
            throw $e;
        }
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
     *
     * @throws \Exception
     */
    public function find($collection, $criteria = [], $fields = [], $limit = null, $offset = 0, $sorts = [], $options = [])
    {
        $this->start();

        try {
            $builtCriteria = $this->buildCriteria($criteria);
            $builtFields   = $this->buildFields($fields);
            $builtSorts    = $this->buildSorts($sorts);

            $cursor = $this->getCollection($collection, $options)->find($builtCriteria);

            if (count($builtFields)) {
                $cursor->fields($builtFields);
            }
            if (count($builtSorts)) {
                $cursor->sort($builtSorts);
            }
            if (is_numeric($offset) && $offset > 0) {
                $cursor->skip($offset);
            }
            if (is_numeric($limit) && $limit > 0) {
                $cursor->limit($limit);
            }

            list ($startDate, $endDate) = $this->stop();
            $this->dispatch('database.query.executed', new DatabaseQueryEvent('find', 'db.'.$collection.'.find('.json_encode($builtCriteria).', '.json_encode($builtFields).')', ['criteria' => $builtCriteria, 'fields' => $builtFields, 'limit' => $limit, 'sort' => $builtSorts, 'skip' => $offset, 'options' => $options], $startDate, $endDate, $cursor));

            return $cursor;
        } catch (\Exception $e) {
            list ($startDate, $endDate) = $this->stop();
            $this->dispatch('database.query.executed', new DatabaseQueryEvent('find', 'db.'.$collection.'.find('.json_encode($criteria).', '.json_encode($fields).')', ['rawCriteria' => $criteria, 'rawFields' => $fields, 'limit' => $limit, 'rawSort' => $sorts, 'skip' => $offset, 'options' => $options], $startDate, $endDate, null, $e));
            throw $e;
        }
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
     *
     * @throws \Exception
     */
    public function findOne($collection, $criteria = [], $fields = [], $options = [])
    {
        $this->start();

        try {
            $builtCriteria = $this->buildCriteria($criteria);
            $builtFields   = $this->buildFields($fields);

            $result = $this->getCollection($collection, $options)->findOne(
                $builtCriteria,
                $builtFields
            );

            list ($startDate, $endDate) = $this->stop();
            $this->dispatch('database.query.executed', new DatabaseQueryEvent('findOne', 'db.'.$collection.'.findOne('.json_encode($builtCriteria).', '.json_encode($builtFields).')', ['criteria' => $builtCriteria, 'fields' => $builtFields, 'options' => $options], $startDate, $endDate, $result));

            return $result;
        } catch (\Exception $e) {
            list ($startDate, $endDate) = $this->stop();
            $this->dispatch('database.query.executed', new DatabaseQueryEvent('findOne', 'db.'.$collection.'.findOne('.json_encode($criteria).', '.json_encode($fields).')', ['rawCriteria' => $criteria, 'rawFields' => $fields, 'options' => $options], $startDate, $endDate, null, $e));
            throw $e;
        }
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
        return $this->getCollection($collection, $options)->createIndex(
            is_array($fields) ? $fields : [$fields => true],
            $index
        );
    }
    /**
     * @param string|null $name
     *
     * @return array
     */
    public function getStatistics($name = null)
    {
        return $this->getMongoClient()
            ->selectDB(null === $name ? $this->getDatabaseName() : $name)
            ->command(['dbStats' => true])
        ;
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
            throw $this->createMalformedException('Malformed id');
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

        foreach ($criteria as $k => $v) {
            if ('*notempty*' === $v) {
                $criteria[$k] = ['$exists' => true];
            }
            if ('*empty*' === $v) {
                $criteria[$k] = ['$exists' => false];
            }
        }

        return $criteria;
    }
    /**
     * Ensure fields are well formed (array).
     *
     * @param array|mixed $fields
     *
     * @return array
     */
    protected function buildFields($fields)
    {
        $cleanedFields = [];

        if (!is_array($fields)) {
            return $cleanedFields;
        }

        foreach ($fields as $k => $v) {
            if (is_numeric($k)) {
                if (!is_string($v)) {
                    $v = (string) $v;
                }
                $cleanedFields[$v] = true;
            } else {
                if (!is_bool($v)) {
                    $v = (bool) $v;
                }
                $cleanedFields[$k] = $v;
            }
        }

        return $cleanedFields;
    }
    /**
     * Ensure sorts are well formed (array).
     *
     * @param array|mixed $sorts
     *
     * @return array
     */
    protected function buildSorts($sorts)
    {
        $cleanedSorts = [];

        if (!is_array($sorts)) {
            return $cleanedSorts;
        }

        foreach ($sorts as $k => $v) {
            if (is_numeric($k)) {
                if (!is_string($v)) {
                    $v = (string) $v;
                }
                $cleanedSorts[$v] = 1;
            } else {
                $v = ((int) $v) === -1 ? -1 : 1;
                $cleanedSorts[$k] = $v;
            }
        }

        return $cleanedSorts;
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
     * @return float
     */
    protected function start()
    {
        $now = microtime(true);

        $this->timers[] = $now;

        return $now;
    }
    /**
     * @return float[]
     *
     * @throws \Exception
     */
    protected function stop()
    {
        if (!count($this->timers)) {
            $this->start();
            //throw $this->createRequiredException('No timer started'.(new \Exception("toto"))->getTraceAsString());
        }

        $endDate = microtime(true);

        return [array_pop($this->timers), $endDate];
    }
}
