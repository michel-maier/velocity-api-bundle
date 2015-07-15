<?php

namespace Velocity\Bundle\ApiBundle\Service;

use Velocity\Bundle\ApiBundle\Traits\ServiceTrait;
use Velocity\Bundle\ApiBundle\Traits\LoggerServiceAwareTrait;
use Velocity\Bundle\ApiBundle\Traits\DatabaseServiceAwareTrait;
use Velocity\Bundle\ApiBundle\Traits\TranslatorAwareTrait;
use Symfony\Component\Translation\TranslatorInterface;
use /** @noinspection PhpUndefinedClassInspection */ MongoDuplicateKeyException;

class RepositoryService
{
    use ServiceTrait;
    use LoggerServiceAwareTrait;
    use DatabaseServiceAwareTrait;
    use TranslatorAwareTrait;
    /**
     * @param DatabaseService     $databaseService
     * @param TranslatorInterface $translator
     * @param string              $collectionName
     * @param string              $idField
     */
    public function __construct(DatabaseService $databaseService, TranslatorInterface $translator, $collectionName, $idField = '_id')
    {
        $this->setDatabaseService($databaseService);
        $this->setTranslator($translator);
        $this->setCollectionName($collectionName);
        $this->setIdField($idField);
    }
    /**
     * @param string $collectionName
     *
     * @return $this
     */
    protected function setCollectionName($collectionName)
    {
        return $this->setParameter('collectionName', $collectionName);
    }
    /**
     * @return string
     */
    public function getCollectionName()
    {
        return $this->getParameterIfExists('collectionName');
    }
    /**
     * @param string $name
     *
     * @return \MongoCollection
     */
    protected function getCollection($name = null)
    {
        if (!$name) $name = $this->getCollectionName();

        return $this->getDatabaseService()->getCollection($name);
    }
    /**
     * @return string
     */
    public function getIdField()
    {
        return $this->getParameter('idField');
    }
    /**
     * @param string $idField
     *
     * @return $this
     */
    protected function setIdField($idField)
    {
        return $this->setParameter('idField', $idField);
    }
    /**
     * @param array $data
     *
     * @return array
     */
    public function createDocument($data)
    {
        $wasObject = false;
        $realData  = $data;

        if (is_object($realData)) {
            $wasObject = true;
            $realData  = get_object_vars($realData);
        }

        $unsaved = null;

        if (!is_array($realData)) $realData = [];
        if ('_id' !== $this->getIdField()) {
            if (isset($realData[$this->getIdField()])) {
                $this->checkDocumentNotExist($realData[$this->getIdField()]);
            }
        } elseif (isset($realData['id'])) {
            $realData['_id'] = $this->buildId($realData['id']);
            unset($realData['id']);
        }
        if (isset($realData['unsaved'])) {
            $unsaved = $realData['unsaved'];
        }
        unset($realData['unsaved']);

        $realData = $this->convert($realData);
        $this->insert($realData, ['new' => true]);

        if (null !== $unsaved) {
            $realData['unsaved'] = $unsaved;
        }

        if ('_id' === $this->getIdField()) {
            $realData['id'] = (string)$realData['_id'];
            unset($realData['_id']);
        }

        $realData = $this->revert($realData);

        if ($wasObject) {
            foreach($realData as $k => $v) {
                $data->$k = $v;
            }
        } else {
            $data = $realData;
        }

        return $data;
    }
    /**
     * @param mixed $data
     *
     * @return mixed
     */
    protected function convert($data)
    {
        foreach($data as $k => $v) {
            if (null === $v) {
                unset($data[$k]);
                continue;
            }
            if ('Date' === substr($k, -4)) {
                $data = $this->convertDataDateTimeFieldToMongoDateWithTimeZone($data, $k);
            } elseif (is_array($data[$k])) {
                $data[$k] = $this->convert($data[$k]);
            }
        }

        return $data;
    }
    /**
     * @param mixed $doc
     *
     * @return mixed
     */
    protected function revert($doc)
    {
        foreach(array_keys($doc) as $k) {
            if (!isset($doc[$k])) continue; // if key was removed by previous iteration
            if ('Date' === substr($k, -4)) {
                $doc = $this->revertDocumentMongoDateWithTimeZoneFieldToDateTime($doc, $k);
            } elseif (is_array($doc[$k])) {
                $doc[$k] = $this->revert($doc[$k]);
            }
        }

        return $doc;
    }
    /**
     * @param array $bulkData
     *
     * @return array
     */
    public function createDocumentBulk($bulkData)
    {
        if (!is_array($bulkData)) $bulkData = [];

        foreach($bulkData as $i => $data) {
            if (!is_array($data)) $data = [];

            if ($this->getIdField()) {
                if (isset($data[$this->getIdField()])) {
                    $this->checkDocumentNotExist($data[$this->getIdField()]);
                }
            } elseif (isset($data['id'])) {
                $data['_id'] = $this->buildId($data['id']);
                unset($data['id']);
            }
            $bulkData[$i] = $data;
        }

        $this->batchInsert($bulkData, ['new' => true]);

        foreach(array_keys($bulkData) as $i) {
            if (!$this->getIdField()) {
                $bulkData[$i]['id'] = (string)$bulkData[$i]['_id'];
            }
            unset($bulkData[$i]['_id']);
        }


        return $bulkData;
    }
    /**
     * @param string $id
     *
     * @return array
     */
    protected function getIdCriteria($id)
    {
        if ($this->getIdField()) {
            $criteria = [$this->getIdField() => $id];
        } else {
            $criteria = ['_id' => $this->buildId($id)];
        }

        return $criteria;
    }
    /**
     * @param string $id
     * @param array  $fields
     * @param array  $criteria
     *
     * @return null|array
     *
     * @throws \Exception
     */
    public function getDocument($id, $fields = [], $criteria = [])
    {
        $idField = $this->getIdField();

        if ($idField && '_id' !== $idField) {
            return $this->getDocumentBy($idField, $id, $fields, $criteria);
        } else {
            try {
                return $this->getDocumentBy('_id', $this->buildId($id), $fields, $criteria);
            } catch (\Exception $e) {
                if (412 === $e->getCode() && 'Malformed id' === $e->getMessage()) {
                    $this->throwException(
                        404, "Unknown %s with %s '%s'", $this->getCollectionName(), 'id', $id
                    );
                    return $this; // never reached
                } else {
                    throw $e;
                }
            }
        }
    }
    /**
     * @param string $fieldName
     * @param mixed  $fieldValue
     * @param array  $fields
     * @param array  $criteria
     *
     * @return null|array
     *
     * @throws \Exception
     */
    public function getDocumentBy($fieldName, $fieldValue, $fields = [], $criteria = [])
    {
        try {
            $doc = $this->findOne([$fieldName => $fieldValue] + $criteria, $fields);
        } catch (\Exception $e) {
            if (412 === $e->getCode() && 'Malformed id' === $e->getMessage()) {
                $doc = null;
            } else {
                throw $e;
            }
        }

        if (!$doc) $this->throwException(
            404, "Unknown %s with %s '%s'", $this->getCollectionName(), $fieldName, $fieldValue
        );

        if ('_id' === $this->getIdField()) {
            $doc['id'] = (string)$doc['_id'];
        }

        unset($doc['_id']);

        return $this->revert($doc);
    }
    /**
     * @param string $id
     *
     * @return bool
     */
    public function hasDocument($id)
    {
        return null !== $this->findOne($this->getIdCriteria($id));
    }
    /**
     * @param string $id
     *
     * @return $this
     */
    public function checkDocumentExist($id)
    {
        if (!$this->hasDocument($id)) {
            $this->throwException("Unknown %s '%s'", $this->getCollectionName(), $id);
        }

        return $this;
    }
    /**
     * @param string $id
     *
     * @return $this
     */
    public function checkDocumentNotExist($id)
    {
        if ($this->hasDocument($id)) {
            $this->throwTranslatedException(412, "{type} '{id}' already exist", ['type' => $this->getCollectionName(), 'id' => $id]);
        }

        return $this;
    }
    /**
     * @param array    $criteria
     *
     * @return int
     */
    public function countDocuments($criteria = [])
    {
        return $this->getDocumentCursor($criteria, ['id'])->count();
    }
    /**
     * @param array    $criteria
     * @param array    $fields
     * @param int|null $limit
     * @param int      $offset
     * @param array    $sorts
     * @param \Closure $eachCallback
     *
     * @return array
     */
    public function listDocuments(
        $criteria = [], $fields = [], $limit = null, $offset = 0, $sorts = [], \Closure $eachCallback = null
    )
    {
        $cursor    = $this->getDocumentCursor($criteria, $fields, $limit, $offset, $sorts);
        $documents = [];

        foreach($cursor as $document) {

            $idField = $this->getIdField();

            if ('_id' === $idField) {
                $document['id'] = (string)$document['_id'];
                $idField = 'id';
            }

            unset($document['_id']);
            $document = $this->revert($document);
            $currentId = $document[$idField];
            if ($eachCallback) $document = $eachCallback($document);
            $documents[$currentId] = $document;
        }

        return $documents;
    }

    /**
     * @param array    $criteria
     * @param array    $fields
     * @param int|null $limit
     * @param int      $offset
     * @param array    $sorts
     *
     * @return \MongoCursor
     */
    public function getDocumentCursor(
        $criteria = [], $fields = [], $limit = null, $offset = 0, $sorts = []
    )
    {
        if (!$this->getIdField()) {
            if (isset($criteria['id'])) {
                if (is_array($criteria['id'])) {
                    $or = [];
                    foreach ($criteria['id'] as $i => $_id) {
                        $or[] = ['_id' => $this->buildId($_id)];
                    }
                    $criteria['$or'] = $or;
                    unset($criteria['id']);
                } else {
                    $criteria['_id'] = $this->buildId($criteria['id']);
                    unset($criteria['id']);
                }
            }
        }

        foreach ($criteria as $criteriaKey => $criteriaValue) {
            if (false !== strpos($criteriaKey, ':')) {
                unset($criteria[$criteriaKey]);
                list($criteriaKey, $criteriaValueType) = explode(':', $criteriaKey, 2);
                switch (trim($criteriaValueType)) {
                    case 'int':
                        $criteriaValue = (int)$criteriaValue;
                        break;
                    case 'string':
                        $criteriaValue = (string)$criteriaValue;
                        break;
                    case 'bool':
                        $criteriaValue = (bool)$criteriaValue;
                        break;
                    case 'array':
                        $criteriaValue = json_decode($criteriaValue, true);
                        break;
                    case 'float':
                        $criteriaValue = (double)$criteriaValue;
                        break;
                    default:
                        break;
                }
                $criteria[$criteriaKey] = $criteriaValue;
            }
            if ('*empty*' === $criteriaValue) {
                $criteriaValue = ['$exists' => false];
            } elseif ('*notempty*' === $criteriaValue) {
                $criteriaValue = ['$exists' => true];
            }
            $criteria[$criteriaKey] = $criteriaValue;
        }

        return $this->find($criteria, $fields, $limit, $offset, $sorts);
    }
    /**
     * @param string $id
     *
     * @return array
     */
    public function deleteDocument($id)
    {
        $this->remove($this->getIdCriteria($id));

        return $this;
    }
    /**
     * @param array $criteria
     *
     * @return array
     */
    public function deleteDocuments($criteria = [])
    {
        if (!$this->getIdField()) {
            if (isset($criteria['id'])) {
                $criteria['_id'] = $this->buildId($criteria['id']);
                unset($criteria['id']);
            }
        }

        $this->remove($criteria);

        return $this;
    }
    /**
     * @param string $id
     * @param string $property
     * @param mixed  $value
     *
     * @return $this
     */
    public function setDocumentProperty($id, $property, $value)
    {
        $this->updateDocument($id, ['$set' => [$property => $value]]);

        return $this;
    }
    /**
     * @param string $id
     * @param array  $values
     *
     * @return $this
     */
    public function setDocumentProperties($id, $values)
    {
        if (!count($values)) {
            return $this;
        }

        $this->updateDocument($id, ['$set' => $values]);

        return $this;
    }
    /**
     * @param string $id
     * @param string $property
     * @param mixed  $value
     *
     * @return $this
     */
    public function incrementDocumentProperty($id, $property, $value = 1)
    {
        $this->updateDocument($id, ['$inc' => [$property => $value]]);

        return $this;
    }
    /**
     * @param string $id
     * @param array  $values
     *
     * @return $this
     */
    public function incrementMultipleDocumentProperties($id, $values)
    {
        if (!count($values)) {
            return $this;
        }

        $this->updateDocument($id, ['$inc' => $values]);

        return $this;

    }
    /**
     * @param string       $id
     * @param string|array $property
     *
     * @return $this
     */
    public function unsetDocumentProperty($id, $property)
    {
        if (!is_array($property)) {
            $property = [$property];
        }

        $this->updateDocument($id, ['$unset' => array_fill_keys($property, '')]);

        return $this;
    }
    /**
     * @param string $id
     * @param array  $data
     *
     * @return $this
     */
    public function updateDocument($id, $data)
    {
        $this->update($this->getIdCriteria($id), $data);

        return $this;
    }
    /**
     * @param string $id
     * @param string $property
     *
     * @return mixed
     *
     * @throws \RuntimeException
     */
    public function getDocumentProperty($id, $property)
    {
        $document = $this->getDocument($id, [$property]);

        $value = $document;

        foreach(explode('.', $property) as $key) {
            if (!isset($value[$key]))
                $this->throwException(
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
     * @param string $id
     * @param string $property
     *
     * @return bool
     */
    public function hasDocumentProperty($id, $property)
    {
        $document = $this->getDocument($id, [$property]);

        $value = $document;

        foreach(explode('.', $property) as $key) {
            if (!isset($value[$key])) return false;
            $value = $value[$key];
        }

        return true;
    }
    /**
     * @param string $id
     * @param string $property
     *
     * @return $this
     */
    public function checkDocumentPropertyExist($id, $property)
    {
        if (!$this->hasDocumentProperty($id, $property)) {
            $this->throwException(
                412,
                "Unknown %s in %s '%s'", str_replace('.', ' ', $property), $this->getCollectionName(), $id
            );
        }

        return $this;
    }
    /**
     * @param array $criteria
     * @param array $data
     * @param array $options
     *
     * @return bool
     */
    protected function update($criteria, $data = [], $options = [])
    {
        return $this->getCollection()->update($criteria, $data, $options);
    }
    /**
     * @param array $criteria
     * @param array $options
     *
     * @return bool
     */
    protected function remove($criteria, $options = [])
    {
        return $this->getCollection()->remove($criteria, $options);
    }
    /**
     * @param array $data
     * @param array $options
     *
     * @return array|bool
     *
     * @throws \Exception
     */
    protected function insert($data, $options = [])
    {
        try {
            return $this->getCollection()->insert($data, $options);
        } catch (\Exception $e) {
            /** @noinspection PhpUndefinedClassInspection */
            if ($e instanceof MongoDuplicateKeyException) {
                $this->throwException(412, "{type} already exist", ['{type}' => $this->translate($this->getCollectionName())]);
            }
            throw $e;
        }
    }
    /**
     * @param array $bulkData
     * @param array $options
     *
     * @return array|bool
     */
    protected function batchInsert($bulkData, $options = [])
    {
        return $this->getCollection()->batchInsert($bulkData, $options);
    }
    /**
     * @param array $fields
     *
     * @return array
     */
    protected function buildFieldList($fields)
    {
        $_fields = [];

        if (is_array($fields)   && count($fields)) {
            foreach($fields as $field) {
                if ('!' === substr($field, 0, 1)) {
                    $_fields[substr($field, 1)] = false;
                } else {
                    $_fields[$field] = true;
                }
            }
        }

        return $_fields;
    }
    /**
     * @param array    $criteria
     * @param array    $fields
     * @param int|null $limit
     * @param int      $offset
     * @param array    $sorts
     *
     * @return \MongoCursor
     */
    protected function find($criteria, $fields = [], $limit = null, $offset = 0, $sorts = [])
    {
        $cursor = $this->getCollection()->find($criteria);

        $_fields = $this->buildFieldList($fields);

        if (is_array($_fields)  && count($_fields)) $cursor->fields($_fields);
        if (is_array($sorts)    && count($sorts))   $cursor->sort(array_map(function ($a) { return (int)$a;}, $sorts));
        if (is_numeric($offset) && $offset > 0)     $cursor->skip($offset);
        if (is_numeric($limit)  && $limit > 0)      $cursor->limit($limit);

        return $cursor;
    }
    /**
     * @param array $criteria
     * @param array $fields
     *
     * @return \MongoCursor
     */
    protected function findOne($criteria, $fields = [])
    {
        return $this->getCollection()->findOne($criteria, $this->buildFieldList($fields));
    }
    /**
     * @param array  $data
     * @param string $fieldName
     *
     * @return array
     */
    protected function convertDataDateTimeFieldToMongoDateWithTimeZone($data, $fieldName)
    {
        if (!isset($data[$fieldName])) $this->throwException(412, "Missing date time field '%s'", $fieldName);

        if (!$data[$fieldName] instanceof \DateTime)
            $this->throwException(412, "Field '%s' must be a valid DateTime", $fieldName);

        /** @var \DateTime $date */
        $date = $data[$fieldName];

        $data[$fieldName] = new \MongoDate($date->getTimestamp());
        $data[sprintf('%s_tz', $fieldName)] = $date->getTimezone()->getName();

        return $data;
    }
    /**
     * @param array  $doc
     * @param string $fieldName
     *
     * @return array
     */
    protected function revertDocumentMongoDateWithTimeZoneFieldToDateTime($doc, $fieldName)
    {
        if (!isset($doc[$fieldName])) $this->throwException(412, "Missing mongo date field '%s'", $fieldName);

        if (!isset($doc[sprintf('%s_tz', $fieldName)])) {
            $doc[sprintf('%s_tz', $fieldName)] = date_default_timezone_get();
        }

        if (!$doc[$fieldName] instanceof \MongoDate)
            $this->throwException(412, "Field '%s' must be a valid MongoDate", $fieldName);

        /** @var \MongoDate $mongoDate */
        $mongoDate = $doc[$fieldName];

        $date = new \DateTime(sprintf('@%d', $mongoDate->sec), new \DateTimeZone('UTC'));

        $doc[$fieldName] = date_create_from_format(
            \DateTime::ISO8601,
            preg_replace('/(Z$|\+0000$)/', $doc[sprintf('%s_tz', $fieldName)], $date->format(\DateTime::ISO8601))
        );

        unset($doc[sprintf('%s_tz', $fieldName)]);

        return $doc;
    }
    /**
     * @param string $id
     *
     * @return \MongoId
     */
    protected function buildId($id)
    {
        if (!preg_match('/^[a-f0-9]{24}$/', $id)) {
            $this->throwException(412, 'Malformed id');
        }

        return new \MongoId($id);
    }
    /**
     * @param array $index
     *
     * @return RepositoryService
     */
    public function createIndex($index)
    {
        return $this->createIndexes([$index]);
    }
    /**
     * @param array $indexes
     *
     * @return $this
     */
    public function createIndexes($indexes)
    {
        foreach($indexes as $index) {
            if (is_string($index)) {
                $index = ['field' => $index];
            }
            if (!is_array($index)) {
                $this->throwException(412, "Malformed index definition");
            }
            $fields = $index['field'];
            unset($index['field']);
            $this->getCollection()->ensureIndex(is_array($fields) ? $fields : [$fields => true], $index);
        }

        return $this;
    }
}