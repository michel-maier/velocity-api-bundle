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
use MongoCursor;
use MongoDuplicateKeyException;
use Velocity\Bundle\ApiBundle\Traits\ServiceAware;
use Velocity\Bundle\ApiBundle\Traits\ServiceTrait;
use Velocity\Bundle\ApiBundle\Traits\LoggerAwareTrait;
use Velocity\Bundle\ApiBundle\Traits\TranslatorAwareTrait;
use Velocity\Bundle\ApiBundle\Repository\RepositoryInterface;

class RepositoryService implements RepositoryInterface
{
    use ServiceTrait;
    use LoggerAwareTrait;
    use TranslatorAwareTrait;
    use ServiceAware\DatabaseServiceAwareTrait;

    /**
     * Set the underlying collection name.
     *
     * @param string $collectionName
     *
     * @return $this
     */
    public function setCollectionName($collectionName)
    {
        return $this->setParameter('collectionName', $collectionName);
    }
    /**
     * Return the underlying collection name.
     *
     * @return string
     */
    public function getCollectionName()
    {
        return $this->getParameter('collectionName');
    }
    /**
     * Create a new document based on specified data.
     *
     * @param array $data
     * @param array $options
     *
     * @return array
     *
     * @throws Exception
     */
    public function create($data, $options = [])
    {
        try {
            return $this->getDatabaseService()
                ->insert($this->getCollectionName(), $data, $options + ['new' => true]);
        } catch (MongoDuplicateKeyException $e) {
            throw $this->createException(
                412,
                "{type} already exist",
                ['{type}' => $this->translate($this->getCollectionName())]
            );
        }
    }
    /**
     * Create multiple new documents based on specified bulk data.
     *
     * @param array $bulkData
     * @param array $options
     *
     * @return array
     *
     * @throws Exception
     */
    public function createBulk($bulkData, $options = [])
    {
        try {
            return $this->getDatabaseService()->bulkInsert(
                $this->getCollectionName(), $bulkData, $options + ['new' => true]
            );
        } catch (MongoDuplicateKeyException $e) {
            throw $this->createException(
                412,
                "{type} already exist",
                ['{type}' => $this->translate($this->getCollectionName())]
            );
        }
    }
    /**
     * Retrieve specified document by id.
     *
     * @param string $id
     * @param array $fields
     * @param array $options
     *
     * @return array
     *
     * @throws Exception
     */
    public function get($id, $fields = [], $options = [])
    {
        return $this->getBy('_id', $id, $fields, $options);
    }
    /**
     * Retrieve specified document by specified field.
     *
     * @param string $fieldName
     * @param mixed $fieldValue
     * @param array $fields
     * @param array $options
     *
     * @return array
     *
     * @throws Exception
     */
    public function getBy($fieldName, $fieldValue, $fields = [], $options = [])
    {
        $doc = $this->getDatabaseService()->findOne(
            $this->getCollectionName(), [$fieldName => $fieldValue], $fields, $options
        );

        if (null === $doc) throw $this->createException(
            404,
            "Unknown %s with %s '%s'",
            $this->getCollectionName(), $fieldName, $fieldValue
        );

        return $doc;
    }
    /**
     * Retrieve random document.
     *
     * @param array $fields
     * @param array $criteria
     * @param array $options
     *
     * @return array
     *
     * @throws Exception
     */
    public function getRandom($fields = [], $criteria = [], $options = [])
    {
        srand(microtime(true));

        $docs     = $this->find($criteria, ['_id']);
        $index   = rand(0, $docs->count() - 1);

        $current = 0;

        $document = null;

        foreach($docs as $doc) {
            if ($current === $index) {
                $document = $doc;
                break;
            }
            $current++;
        }

        return $this->get($document['_id'], $fields, $criteria);
    }
    /**
     * Test if specified document exist.
     *
     * @param string $id
     * @param array $options
     *
     * @return bool
     */
    public function has($id, $options = [])
    {
        return null !== $this->getDatabaseService()->findOne(
            $this->getCollectionName(), ['_id' => $id], [], $options
        );
    }
    /**
     * Test if specified document not exist.
     *
     * @param string $id
     * @param array $options
     *
     * @return bool
     */
    public function hasNot($id, $options = [])
    {
        return !$this->has($id, $options);
    }
    /**
     * Check if specified document exist.
     *
     * @param string $id
     * @param array $options
     *
     * @return $this
     *
     * @throws Exception
     */
    public function checkExist($id, $options = [])
    {
        if (!$this->has($id)) {
            throw $this->createException(
                404,
                "Unknown %s '%s'",
                $this->getCollectionName(), $id
            );
        }

        return $this;
    }
    /**
     * Check if specified document not exist.
     *
     * @param string $id
     * @param array $options
     *
     * @return $this
     *
     * @throws Exception
     */
    public function checkNotExist($id, $options = [])
    {
        if ($this->has($id, $options)) {
            throw $this->createException(
                412,
                "{type} '{id}' already exist",
                ['type' => $this->getCollectionName(), 'id' => $id]
            );
        }

        return $this;
    }
    /**
     * Count documents matching specified criteria.
     *
     * @param array $criteria
     * @param array $options
     *
     * @return int
     */
    public function count($criteria = [], $options = [])
    {
        return $this->getDatabaseService()
            ->count($this->getCollectionName(), $criteria);
    }
    /**
     * Retrieve the documents matching the specified criteria, and optionally filter page.
     *
     * @param array $criteria
     * @param array $fields
     * @param int|null $limit
     * @param int $offset
     * @param array $sorts
     * @param array $options
     *
     * @return MongoCursor
     */
    public function find(
        $criteria = [], $fields = [], $limit = null, $offset = 0, $sorts = [],
        $options = []
    )
    {
        return $this->getDatabaseService()->find(
            $this->getCollectionName(), $criteria, $fields, $limit, $offset, $sorts, $options
        );
    }
    /**
     * Delete the specified document.
     *
     * @param string $id
     * @param array $options
     *
     * @return array
     */
    public function delete($id, $options = [])
    {
        $this->getDatabaseService()->remove(
            $this->getCollectionName(), ['_id' => $id], ['justOne' => true] + $options
        );

        return $this;
    }
    /**
     * Delete documents matching specified criteria.
     *
     * @param array $criteria
     * @param array $options
     *
     * @return array
     */
    public function deleteFound($criteria, $options = [])
    {
        $this->getDatabaseService()->remove($criteria, $options);

        return $this;
    }
    /**
     * Set the specified property of the specified document.
     *
     * @param string $id
     * @param string $property
     * @param mixed $value
     * @param array $options
     *
     * @return $this
     */
    public function setProperty($id, $property, $value, $options = [])
    {
        return $this->update($id, ['$set' => [$property => $value]], $options);
    }
    /**
     * Set the specified properties of the specified document.
     *
     * @param string $id
     * @param array $values
     * @param array $options
     *
     * @return $this
     */
    public function setProperties($id, $values, $options = [])
    {
        if (!count($values)) {
            return $this;
        }

        return $this->update($id, ['$set' => $values]);
    }
    /**
     * Increment specified property of the specified document.
     *
     * @param string $id
     * @param string $property
     * @param mixed $value
     * @param array $options
     *
     * @return $this
     */
    public function incrementProperty($id, $property, $value = 1, $options = [])
    {
        return $this->update($id, ['$inc' => [$property => $value]], $options);
    }
    /**
     * Decrement specified property of the specified document.
     *
     * @param string $id
     * @param string $property
     * @param mixed $value
     * @param array $options
     *
     * @return $this
     */
    public function decrementProperty($id, $property, $value = 1, $options = [])
    {
        return $this->update($id, ['$inc' => [$property => - $value]], $options);
    }
    /**
     * Increment specified properties of the specified document.
     *
     * @param string $id
     * @param array $values
     * @param array $options
     *
     * @return $this
     */
    public function incrementProperties($id, $values, $options = [])
    {
        if (!count($values)) {
            return $this;
        }

        return $this->update($id, ['$inc' => $values], $options);
    }
    /**
     * Decrement specified properties of the specified document.
     *
     * @param string $id
     * @param array $values
     * @param array $options
     *
     * @return $this
     */
    public function decrementProperties($id, $values, $options = [])
    {
        if (!count($values)) {
            return $this;
        }

        return $this->update($id, [
            '$inc' => array_map(function ($v) { return - $v;}, $values)],
            $options
        );
    }
    /**
     * Unset the specified property of the specified document.
     *
     * @param string $id
     * @param string|array $property
     * @param array $options
     *
     * @return $this
     */
    public function unsetProperty($id, $property, $options = [])
    {
        return $this->update($id, ['$unset' => [$property => '']], $options);
    }
    /**
     * Update the specified document with the specified data.
     *
     * @param string $id
     * @param array $data
     * @param array $options
     *
     * @return $this
     */
    public function update($id, $data, $options = [])
    {
        return $this->update(['_id' => $id], ['$set' => $data], ['upsert' => false] + $options);
    }
    /**
     * Update multiple document specified with their data.
     *
     * @param array $bulkData
     * @param array $options
     *
     * @return array
     */
    public function updateBulk($bulkData, $options = [])
    {
        $docs = [];

        foreach($bulkData as $id => $data) {
            $docs[$id] = $this->update($id, $data, $options);
        }

        return $docs;
    }
    /**
     * Delete multiple document specified with their id.
     *
     * @param array $bulkIds
     * @param array $options
     *
     * @return array
     */
    public function deleteBulk($bulkIds, $options = [])
    {
        $properties  = [];

        foreach($bulkIds as $id) {
            $properties[$id] = ['id' => $id];
        }

        $this->deleteFound(['$or' => array_keys($properties)], $options);

        return $properties;
    }
    /**
     * Return the specified property of the specified document.
     *
     * @param string $id
     * @param string $property
     * @param array $options
     *
     * @return mixed
     *
     * @throws Exception
     */
    public function getProperty($id, $property, $options = [])
    {
        $document = $this->get($id, [$property], $options);
        $value    = $document;

        foreach(explode('.', $property) as $key) {
            if (!isset($value[$key]))
                throw $this->createException(
                    412,
                    "Unknown %s in %s '%s'",
                    str_replace('.', ' ', $property),
                    $this->getCollectionName(),
                    $id
                );

            $value = $value[$key];
        }

        return $value;
    }
    /**
     * Test if specified property is present in specified document.
     *
     * @param string $id
     * @param string $property
     * @param array $options
     *
     * @return bool
     */
    public function hasProperty($id, $property, $options = [])
    {
        $document = $this->get($id, [$property], $options);
        $value    = $document;

        foreach(explode('.', $property) as $key) {
            if (!isset($value[$key])) {
                return false;
            }
            $value = $value[$key];
        }

        return true;
    }
    /**
     * Check if specified property is present in specified document.
     *
     * @param string $id
     * @param string $property
     * @param array $options
     *
     * @return $this
     *
     * @throws Exception
     */
    public function checkPropertyExist($id, $property, $options = [])
    {
        if (!$this->hasProperty($id, $property, $options)) {
            throw $this->createException(
                412,
                "Unknown %s in %s '%s'",
                str_replace('.', ' ', $property),
                $this->getCollectionName(),
                $id
            );
        }

        return $this;
    }
    /**
     * Create the specified index.
     *
     * @param array $index
     * @param array $options
     *
     * @return $this
     */
    public function createIndex($index, $options = [])
    {
        return $this->createIndexes([$index]);
    }
    /**
     * Create the specified indexes.
     *
     * @param array $indexes
     * @param array $options
     *
     * @return $this
     *
     * @throws Exception
     */
    public function createIndexes($indexes, $options = [])
    {
        foreach($indexes as $index) {
            if (is_string($index)) {
                $index = ['field' => $index];
            }
            if (!is_array($index)) {
                throw $this->createException(412, "Malformed index definition");
            }
            $fields = $index['field'];
            unset($index['field']);
            $this->getDatabaseService()
                ->ensureIndex($this->getCollectionName(), $fields, $index, $options);
        }

        return $this;
    }
}