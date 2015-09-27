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
use Velocity\Bundle\ApiBundle\RepositoryInterface;
use Velocity\Core\Traits\ServiceTrait;
use Velocity\Core\Traits\LoggerAwareTrait;
use Velocity\Core\Traits\TranslatorAwareTrait;

/**
 * Repository Service.
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
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
            throw $this->createDuplicatedException('%s already exist', ucfirst($this->getCollectionName()));
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
                $this->getCollectionName(),
                $bulkData,
                $options + ['new' => true]
            );
        } catch (MongoDuplicateKeyException $e) {
            throw $this->createDuplicatedException('%s already exist', ucfirst($this->getCollectionName()));
        }
    }
    /**
     * Retrieve specified document by id.
     *
     * @param string|array $id
     * @param array        $fields
     * @param array        $options
     *
     * @return array
     *
     * @throws Exception
     */
    public function get($id, $fields = [], $options = [])
    {
        if (!is_array($id)) {
            $id = ['_id' => $id];
        }

        $doc = $this->getDatabaseService()->findOne(
            $this->getCollectionName(),
            $id,
            $fields,
            $options
        );

        if (null === $doc) {
            switch (count($id)) {
                case 0:
                    throw $this->createNotFoundException("No %s", $this->getCollectionName());
                case 1:
                    throw $this->createNotFoundException(
                        "Unknown %s with %s '%s'",
                        $this->getCollectionName(),
                        array_keys($id)[0],
                        array_values($id)[0]
                    );
                default:
                    throw $this->createNotFoundException(
                        "Unknown %s with %s",
                        $this->getCollectionName(),
                        json_encode($id)
                    );
            }
        }

        return $doc;
    }
    /**
     * Retrieve specified document by specified field.
     *
     * @param string $fieldName
     * @param mixed  $fieldValue
     * @param array  $fields
     * @param array  $options
     *
     * @return array
     *
     * @throws Exception
     */
    public function getBy($fieldName, $fieldValue, $fields = [], $options = [])
    {
        return $this->get([$fieldName => $fieldValue], $fields, $options);
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

        foreach ($docs as $doc) {
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
     * @param string|array $id
     * @param array        $options
     *
     * @return bool
     */
    public function has($id, $options = [])
    {
        if (!is_array($id)) {
            $id = ['_id' => $id];
        }

        return null !== $this->getDatabaseService()->findOne(
            $this->getCollectionName(),
            $id,
            [],
            $options
        );
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
        return $this->has([$fieldName => $fieldValue], $options);
    }
    /**
     * Test if specified document not exist.
     *
     * @param string|array $id
     * @param array        $options
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
     * @param string|array $id
     * @param array        $options
     *
     * @return $this
     *
     * @throws Exception
     */
    public function checkExist($id, $options = [])
    {
        if (!$this->has($id)) {
            switch (count($id)) {
                case 0:
                    throw $this->createNotFoundException("No %s", $this->getCollectionName());
                case 1:
                    throw $this->createNotFoundException(
                        "Unknown %s with %s '%s'",
                        $this->getCollectionName(),
                        array_keys($id)[0],
                        array_values($id)[0]
                    );
                default:
                    throw $this->createNotFoundException(
                        "Unknown %s with %s",
                        $this->getCollectionName(),
                        json_encode($id)
                    );
            }
        }

        return $this;
    }
    /**
     * @param string $field
     * @param mixed  $value
     * @param array  $options
     *
     * @return $this
     *
     * @throws Exception
     */
    public function checkExistBy($field, $value, $options = [])
    {
        return $this->checkExistByBulk($field, [$value], $options);
    }
    /**
     * @param string $field
     * @param array  $values
     * @param array  $options
     *
     * @return $this
     *
     * @throws Exception
     */
    public function checkExistByBulk($field, array $values, $options = [])
    {
        $docs = $this->find([$field => ['$in' => $values]], ['id', $field], null, 0, [], $options);

        $found = [];

        foreach ($docs as $doc) {
            $found[$doc[$field]] = true;
        }

        $notFound = array_diff($values, array_values($found));

        if (0 < count($notFound)) {
            throw $this->createNotFoundException(
                "Unknown %s %s",
                $this->getCollectionName(),
                join(', ', $notFound)
            );
        }

        return $this;
    }
    /**
     * Check if specified document not exist.
     *
     * @param string|array $id
     * @param array        $options
     *
     * @return $this
     *
     * @throws Exception
     */
    public function checkNotExist($id, $options = [])
    {
        if ($this->has($id, $options)) {
            switch (count($id)) {
                case 0:
                    throw $this->createNotFoundException("Existing %s", $this->getCollectionName());
                case 1:
                    throw $this->createNotFoundException(
                        "%s with %s '%s' already exist",
                        $this->getCollectionName(),
                        array_keys($id)[0],
                        array_values($id)[0]
                    );
                default:
                    throw $this->createNotFoundException(
                        "%s with %s already exist",
                        $this->getCollectionName(),
                        json_encode($id)
                    );
            }
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
     * @param array    $criteria
     * @param array    $fields
     * @param int|null $limit
     * @param int      $offset
     * @param array    $sorts
     * @param array    $options
     *
     * @return MongoCursor
     */
    public function find($criteria = [], $fields = [], $limit = null, $offset = 0, $sorts = [], $options = [])
    {
        return $this->getDatabaseService()->find(
            $this->getCollectionName(),
            $criteria,
            $fields,
            $limit,
            $offset,
            $sorts,
            $options
        );
    }
    /**
     * Delete the specified document.
     *
     * @param string|array $id
     * @param array        $options
     *
     * @return array
     */
    public function delete($id, $options = [])
    {
        if (!is_array($id)) {
            $id = ['_id' => $id];
        }

        $this->getDatabaseService()->remove(
            $this->getCollectionName(),
            $id,
            $options + ['justOne' => true]
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
        return $this->delete($criteria, $options + ['justOne' => false]);
    }
    /**
     * Set the specified property of the specified document.
     *
     * @param string|array $id
     * @param string       $property
     * @param mixed        $value
     * @param array        $options
     *
     * @return $this
     */
    public function setProperty($id, $property, $value, $options = [])
    {
        return $this->alter($id, ['$set' => [$property => $value]], $options);
    }
    /**
     * Set the specified hash property of the specified document.
     *
     * @param string|array $id
     * @param string       $property
     * @param array        $data
     * @param array        $options
     *
     * @return $this
     */
    public function setHashProperty($id, $property, array $data, $options = [])
    {
        return $this->setProperty($id, $property, (object) $data, $options);
    }
    /**
     * Reset the specified list property of the specified document.
     *
     * @param string|array $id
     * @param string       $property
     * @param array        $options
     *
     * @return $this
     */
    public function resetListProperty($id, $property, $options = [])
    {
        return $this->setProperty($id, $property, (object) [], $options);
    }
    /**
     * Set the specified properties of the specified document.
     *
     * @param string|array $id
     * @param array        $values
     * @param array        $options
     *
     * @return $this
     */
    public function setProperties($id, $values, $options = [])
    {
        if (!count($values)) {
            return $this;
        }

        return $this->alter($id, ['$set' => $values]);
    }
    /**
     * Increment specified property of the specified document.
     *
     * @param string|array $id
     * @param string       $property
     * @param mixed        $value
     * @param array        $options
     *
     * @return $this
     */
    public function incrementProperty($id, $property, $value = 1, $options = [])
    {
        return $this->alter($id, ['$inc' => [$property => $value]], $options);
    }
    /**
     * Decrement specified property of the specified document.
     *
     * @param string|array $id
     * @param string       $property
     * @param mixed        $value
     * @param array        $options
     *
     * @return $this
     */
    public function decrementProperty($id, $property, $value = 1, $options = [])
    {
        return $this->alter($id, ['$inc' => [$property => - $value]], $options);
    }
    /**
     * Increment specified properties of the specified document.
     *
     * @param string|array $id
     * @param array        $values
     * @param array        $options
     *
     * @return $this
     */
    public function incrementProperties($id, $values, $options = [])
    {
        if (!count($values)) {
            return $this;
        }

        return $this->alter($id, ['$inc' => $values], $options);
    }
    /**
     * Decrement specified properties of the specified document.
     *
     * @param string|array $id
     * @param array        $values
     * @param array        $options
     *
     * @return $this
     */
    public function decrementProperties($id, $values, $options = [])
    {
        if (!count($values)) {
            return $this;
        }

        return $this->alter($id, ['$inc' => array_map(function ($v) {
            return - $v;
        }, $values), ], $options);
    }
    /**
     * Unset the specified property of the specified document.
     *
     * @param string|array $id
     * @param string|array $property
     * @param array        $options
     *
     * @return $this
     */
    public function unsetProperty($id, $property, $options = [])
    {
        if (!is_array($property)) {
            $property = [$property];
        }

        return $this->alter($id, ['$unset' => array_fill_keys($property, '')], $options);
    }
    /**
     * Update the specified document with the specified data.
     *
     * @param string|array $id
     * @param array        $data
     * @param array        $options
     *
     * @return $this
     */
    public function update($id, $data, $options = [])
    {
        return $this->alter($id, ['$set' => $data], ['upsert' => false] + $options);
    }
    /**
     * Alter (raw update) the specified document with the specified data.
     *
     * @param string|array $id      primary key or criteria array
     * @param array        $data
     * @param array        $options
     *
     * @return $this
     */
    public function alter($id, $data, $options = [])
    {
        $criteria = is_array($id) ? $id : ['_id' => $id];

        return $this->getDatabaseService()->update(
            $this->getCollectionName(),
            $criteria,
            $data,
            ['upsert' => false] + $options
        );
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

        foreach ($bulkData as $id => $data) {
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

        foreach ($bulkIds as $id) {
            $properties[$id] = ['_id' => $id];
        }

        $this->deleteFound(['$or' => array_keys($properties)], $options);

        return $properties;
    }
    /**
     * Return the specified property of the specified document.
     *
     * @param string|array $id
     * @param string       $property
     * @param array        $options
     *
     * @return mixed
     *
     * @throws Exception
     */
    public function getProperty($id, $property, $options = [])
    {
        $fields = [];

        if (isset($options['fields']) && is_array($options['fields']) && count($options['fields'])) {
            foreach ($options['fields'] as $field) {
                $fields[] = $property.'.'.$field;
            }
        } else {
            $fields[] = $property;
        }

        $document = $this->get($id, $fields, $options);
        $value    = $document;

        foreach (explode('.', $property) as $key) {
            if (!isset($value[$key])) {
                if (array_key_exists('default', $options)) {
                    return $options['default'];
                }
                throw $this->createRequiredException(
                    "Unknown %s in %s '%s'",
                    str_replace('.', ' ', $property),
                    $this->getCollectionName(),
                    is_array($id) ? json_encode($id) : $id
                );
            }

            $value = $value[$key];
        }

        return $value;
    }
    /**
     * Return the specified property of the specified document.
     *
     * @param string $id
     * @param string $property
     * @param mixed  $defaultValue
     * @param array  $options
     *
     * @return mixed
     *
     * @throws Exception
     */
    public function getPropertyIfExist($id, $property, $defaultValue = null, $options = [])
    {
        return $this->getProperty($id, $property, ['default' => $defaultValue] + $options);
    }
    /**
     * Return the specified property as a list of the specified document.
     *
     * @param string|array $id
     * @param string       $property
     * @param array        $options
     *
     * @return mixed
     *
     * @throws Exception
     */
    public function getListProperty($id, $property, $options = [])
    {
        $value = $this->getProperty($id, $property, $options);

        if (!is_array($value)) {
            $value = [];
        }

        return $value;
    }
    /**
     * Return the specified property as a hash of the specified document.
     *
     * @param string|array $id
     * @param string       $property
     * @param array        $fields
     * @param array        $options
     *
     * @return mixed
     *
     * @throws Exception
     */
    public function getHashProperty($id, $property, $fields = [], $options = [])
    {
        $value = $this->getProperty($id, $property, ['fields' => $fields] + $options);

        if (!is_array($value)) {
            $value = [];
        }

        return $value;
    }
    /**
     * Test if specified property is present in specified document.
     *
     * @param string|array $id
     * @param string       $property
     * @param array        $options
     *
     * @return bool
     */
    public function hasProperty($id, $property, $options = [])
    {
        $document = $this->get($id, [$property], $options);
        $value    = $document;

        foreach (explode('.', $property) as $key) {
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
     * @param string|array $id
     * @param string       $property
     * @param array        $options
     *
     * @return $this
     *
     * @throws Exception
     */
    public function checkPropertyExist($id, $property, $options = [])
    {
        if (!$this->hasProperty($id, $property, $options)) {
            throw $this->createRequiredException(
                "Unknown %s in %s '%s'",
                str_replace('.', ' ', $property),
                $this->getCollectionName(),
                is_array($id) ? json_encode($id) : $id
            );
        }

        return $this;
    }
    /**
     * Check if specified property is not present in specified document.
     *
     * @param string|array $id
     * @param string       $property
     * @param array        $options
     *
     * @return $this
     *
     * @throws Exception
     */
    public function checkPropertyNotExist($id, $property, $options = [])
    {
        if ($this->hasProperty($id, $property, $options)) {
            throw $this->createDuplicatedException(
                "%s in %s '%s' already exist",
                str_replace('.', ' ', $property),
                $this->getCollectionName(),
                is_array($id) ? json_encode($id) : $id
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
        foreach ($indexes as $index) {
            if (is_string($index)) {
                $index = ['field' => $index];
            }
            if (!is_array($index)) {
                throw $this->createMalformedException('Malformed index definition');
            }
            $fields = $index['field'];
            unset($index['field']);
            $this->getDatabaseService()
                ->ensureIndex($this->getCollectionName(), $fields, $index, $options);
        }

        return $this;
    }
}
