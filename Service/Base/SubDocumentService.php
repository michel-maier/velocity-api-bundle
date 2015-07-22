<?php

/*
 * This file is part of the VELOCITY package.
 *
 * (c) PHPPRO <opensource@phppro.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Velocity\Bundle\ApiBundle\Service\Base;

use Velocity\Bundle\ApiBundle\Traits\ServiceTrait;
use Velocity\Bundle\ApiBundle\Exception\ImportException;
use Velocity\Bundle\ApiBundle\Traits\FormServiceAwareTrait;
use Velocity\Bundle\ApiBundle\Traits\LoggerAwareTrait;
use Velocity\Bundle\ApiBundle\Exception\FormValidationException;

/**
 * Sub Document Service.
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
class SubDocumentService
{
    use ServiceTrait;
    use FormServiceAwareTrait;
    use LoggerAwareTrait;
    /**
     * @var string
     */
    protected $type;
    /**
     * @var string
     */
    protected $subType;
    /**
     * @var array
     */
    protected $fields;
    /**
     * @param string $type
     * @param string $subType
     */
    public function __construct($type = null, $subType = null)
    {
        $this->type =
            $type ? $type : lcfirst(basename(dirname(dirname(str_replace('\\', '/', get_class($this))))));
        $this->subType =
            $subType ? $subType : lcfirst(basename(dirname(str_replace('\\', '/', get_class($this)))));
        $this->fields = [];
    }
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }
    public function setSubType($subType)
    {
        $this->subType = $subType;

        return $this;
    }
    /**
     * @return array
     */
    public function getFields()
    {
        return $this->fields;
    }
    /**
     * @param array $fields
     *
     * @return $this
     */
    public function setFields($fields)
    {
        $this->fields = $fields;

        return $this;
    }
    /**
     * @return string
     */
    public function getSubType()
    {
        return $this->subType;
    }
    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }
    /**
     * @return RepositoryService
     */
    public function getRepository()
    {
        return $this->getService('repository');
    }
    /**
     * @param RepositoryService $repository
     *
     * @return $this
     */
    public function setRepository(RepositoryService $repository)
    {
        return $this->setService('repository', $repository);
    }
    /**
     * @return string
     */
    protected function getRepoKey()
    {
        return sprintf('%ss', $this->getSubType());
    }
    /**
     * @param string $parentId
     * @param string $event
     * @param mixed  $data
     *
     * @return $this
     */
    protected function event($parentId, $event, $data = null)
    {
        return $this->dispatch(
            sprintf('%s.%s.%s', $this->getType(), $this->getSubType(), $event),
            [sprintf('%sId', $this->getType()) => $parentId] + (is_array($data) ? $data : [])
        );
    }
    /**
     * @param array  $data
     * @param array  $cleanData
     * @param string $mode
     * @param bool   $clearMissing
     * @param string $suffix
     * @param array  $options
     *
     * @return array
     */
    protected function validate($data, $cleanData = [], $mode = 'create', $clearMissing = true, $suffix = null, $options = [])
    {
        return $this->getFormService()->validate(
            sprintf('%s.%s%s', $this->getType(), $this->getSubType(), ucfirst($suffix)), $mode, $data, $cleanData, $clearMissing, $options
        );
    }
    /**
     * @param string $type
     * @param array  $data
     * @param array  $cleanData
     * @param string $mode
     * @param bool   $clearMissing
     * @param array  $options
     *
     * @return array
     */
    protected function validateSub($type, $data, $cleanData = [], $mode = 'create', $clearMissing = true, $options = [])
    {
        return $this->getFormService()->validate(
            sprintf('%s.%s.%s', $this->getType(), $this->getSubType(), $type), $mode, $data, $cleanData, $clearMissing, $options
        );
    }
    /**
     * @param string $parentId
     * @param array  $data
     * @param array  $options
     *
     * @return array
     *
     * @override
     */
    protected function onCreateBeforeValidate($parentId, $data, $options = [])
    {
        unset($parentId);
        unset($options);

        return $data;
    }
    /**
     * @param string $parentId
     * @param array  $doc
     *
     * @return array
     *
     * @override
     */
    protected function onCreateBeforeSave($parentId, $doc)
    {
        unset($parentId);

        return $doc;
    }
    /**
     * @param string $parentId
     * @param array  $doc
     *
     * @return array
     *
     * @override
     */
    protected function onCreateAfterSave($parentId, $doc)
    {
        unset($parentId);

        return $doc;
    }
    /**
     * @param string $parentId
     * @param string $id
     * @param array  $data
     * @param array  $options
     *
     * @return array
     *
     * @override
     */
    protected function onUpdateBeforeValidate($parentId, $id, $data, $options = [])
    {
        unset($id);
        unset($parentId);
        unset($options);

        return $data;
    }
    /**
     * @param string $parentId
     * @param string $id
     * @param array  $data
     *
     * @return array
     *
     * @override
     */
    protected function onUpdateBeforeSave($parentId, $id, $data)
    {
        unset($id);
        unset($parentId);

        return $data;
    }
    /**
     * @param string $parentId
     * @param string $id
     * @param array  $data
     *
     * @return array
     *
     * @override
     */
    protected function onUpdateAfterSave($parentId, $id, $data)
    {
        unset($id);
        unset($parentId);

        return $data;
    }
    /**
     * @param string $parentId
     * @param string $id
     *
     * @return $this
     *
     * @override
     */
    protected function onDeleteBeforeSave($parentId, $id)
    {
        unset($id);
        unset($parentId);

        return $this;
    }
    /**
     * @param string $parentId
     * @param string $id
     *
     * @return array
     *
     * @override
     */
    protected function onDeleteAfterSave($parentId, $id)
    {
        unset($id);
        unset($parentId);

        return $this;
    }
    /**
     * @param mixed $parentId
     * @param array $data
     *
     * @return array
     */
    public function prepareCreate($parentId, $data)
    {
        $options = [];

        if (method_exists($this, 'onCreateConvert')) $options['convert'] = [$this, 'onCreateConvert'];

        $data = $this->onCreateBeforeValidate($parentId, $data, $options);

        $doc = $this->validate($data, [], 'create', true, null, $options);

        $this->checkNotExist($parentId, $doc['id']);

        $doc = $this->onCreateBeforeSave($parentId, $doc);

        return $doc;
    }
    /**
     * @param string $parentId
     * @param array  $data
     * @param array  $options
     *
     * @return array
     */
    public function create($parentId, $data, $options = [])
    {
        $doc = $this->prepareCreate($parentId, $data);

        $unsaved = null;

        if (isset($doc['unsaved'])) {
            $unsaved = $doc['unsaved'];
            unset($doc['unsaved']);
        }

        $this->getRepository()
            ->setDocumentProperty($parentId, sprintf('%s.%s', $this->getRepoKey(), $doc['id']), $doc);

        $saved = $doc;

        if (isset($unsaved)) {
            $doc += $unsaved;
        }

        $doc = $this->onCreateAfterSave($parentId, $doc);

        $this->event($parentId, 'created', $doc);

        if ($this->observed('created.full')) $this->event($parentId, 'created.full', $this->get($parentId, $doc['id']) + (isset($unsaved) ? $unsaved : []));

        return $saved + (isset($options['unsaved']) ? $unsaved : []);
    }
    /**
     * @param string $parentId
     * @param array  $data
     * @param array  $options
     *
     * @return array
     */
    public function createOrUpdate($parentId, $data, $options = [])
    {
        if (isset($data['id']) && $this->has($parentId, $data['id'])) {
            $id = $data['id'];
            unset($data['id']);
            $this->update($parentId, $id, $data, $options);
            return ['id' => $id];
        }

        return $this->create($parentId, $data, $options);
    }
    /**
     * @param string $event
     *
     * @return bool
     */
    protected function observed($event)
    {
        return $this->hasListeners(sprintf('%s.%s.%s', $this->getType(), $this->getSubType(), $event));
    }
    /**
     * @param string $parentId
     * @param array  $bulkData
     *
     * @return array
     */
    public function createBulk($parentId, $bulkData)
    {
        if (!is_array($bulkData)) {
            $this->throwException(412, "Missing bulk data");
        }

        if (!count($bulkData)) {
            $this->throwException(412, "No data to process");
        }

        $docs = [];
        $createDatas = [];
        $unsaveds = [];

        $errors = [];

        foreach($bulkData as $i => $data) {
            try {
                $doc = $this->validate($data);

                $this->checkNotExist($parentId, $doc['id']);

                $doc = $this->onCreateBeforeSave($parentId, $doc);

                $unsaveds[$i] = null;

                if (isset($doc['unsaved'])) {
                    $unsaveds[$i] = $doc['unsaved'];
                    unset($doc['unsaved']);
                }

                $docs[sprintf('%s.%s', $this->getRepoKey(), $doc['id'])] = $doc;

                if (isset($unsaveds[$i])) {
                    $doc += $unsaveds;
                }

                $createDatas[$i] = $doc;
            } catch (\Exception $e) {
                $errors[$i] = $e;
            }
        }

        $this->getRepository()->setDocumentProperties($parentId, $docs);

        foreach($createDatas as $i => $doc) {
            $doc = $this->onCreateAfterSave($parentId, $doc);

            $this->event($parentId, 'created', $doc);

            if ($this->observed('created.full')) $this->event($parentId, 'created.full', $this->get($parentId, $doc['id']) + (isset($unsaveds[$i]) ? $unsaveds[$i] : []));
        }

        if (count($errors)) $this->throwImportException($errors);

        return array_values($docs);
    }
    /**
     * @param string $parentId
     * @param array  $bulkData
     * @param array  $options
     *
     * @return array
     */
    public function createOrUpdateBulk($parentId, $bulkData, $options = [])
    {
        if (!is_array($bulkData)) {
            $this->throwException(412, "Missing bulk data");
        }

        if (!count($bulkData)) {
            $this->throwException(412, "No data to process");
        }

        $toCreate = [];
        $toUpdate = [];
        $updateMapping = [];

        foreach($bulkData as $i => $data) {
            if (isset($data['id']) && $this->has($parentId, $data['id'])) {
                $id = $data['id'];
                unset($data['id']);
                $toUpdate[$id] = $data;
                $updateMapping[$id] = $i;
            } else {
                $toCreate[$i] = $data;
            }
        }

        $docs = [];

        if (count($toCreate)) $docs = $this->createBulk($parentId, $toCreate, $options);
        try {
            if (count($toUpdate)) $this->updateBulk($parentId, $toUpdate);
        } catch (ImportException $e) {
            $this->throwImportException($e->getErrors(), $updateMapping);
        }

        return $docs;
    }
    /**
     * @param array      $errors
     * @param null|array $idMapping
     */
    protected function throwImportException(array $errors = [], $idMapping = null)
    {
        if (null !== $idMapping) {
            $_errors = [];
            foreach($errors as $id => $error) {
                $_errors[$idMapping[$id]] = $error;
            }
            $errors = $_errors;
        }

        foreach($errors as $id => $error) {
            if ($error instanceof FormValidationException) {
                $error = $this->getFormService()->getFormErrorsFromException($error);
            } elseif ($error instanceof \Exception) {
                $error = $error->getMessage();
            }
            $errors[$id] = $error;
        }

        throw new ImportException($errors);
    }
    /**
     * @param string $parentId
     * @param array  $bulkData
     * @param array  $options
     *
     * @return array
     */
    public function createOrDeleteBulk($parentId, $bulkData, $options = [])
    {
        if (!is_array($bulkData)) {
            $this->throwException(412, "Missing bulk data");
        }

        if (!count($bulkData)) {
            $this->throwException(412, "No data to process");
        }

        $toCreate = [];
        $toDelete = [];

        foreach($bulkData as $i => $data) {
            if (isset($data['id']) && $this->has($parentId, $data['id'])) {
                $id = $data['id'];
                unset($data['id']);
                $toDelete[$id] = $data;
            } else {
                $toCreate[$i] = $data;
            }
        }

        $docs = [];

        if (count($toCreate)) $docs = $this->createBulk($parentId, $toCreate, $options);
        if (count($toDelete)) $this->deleteBulk($parentId, array_keys($toDelete));

        return $docs;
    }
    /**
     * @param string $parentId
     * @param array  $criteria
     *
     * @return array
     */
    public function count($parentId, $criteria = [])
    {
        if (!$this->getRepository()->hasDocumentProperty($parentId, $this->getRepoKey())) return [];

        $items = $this->getRepository()->getDocumentProperty($parentId, $this->getRepoKey());

        if (!is_array($items) || !count($items)) return [];

        $this->filterItems($items, $criteria);

        return count($items);
    }
    /**
     * @param array    $items
     * @param array    $criteria
     * @param array    $fields
     * @param callable $eachCallback
     *
     * @return $this
     */
    protected function filterItems(&$items, $criteria = [], $fields = [], \Closure $eachCallback = null)
    {
        if (!is_array($fields))   $fields = [];
        if (!is_array($criteria)) $criteria = [];

        $keyFields     = array_fill_keys($fields, true);
        $fieldFiltered = false;

        if (is_array($criteria) && count($criteria) > 0) {
            $fieldFiltered = true;
            foreach($criteria as $criteriaKey => $criteriaValue) {
                if (false !== strpos($criteriaKey, ':')) {
                    list($criteriaKey, $criteriaValueType) = explode(':', $criteriaKey, 2);
                    switch(trim($criteriaValueType)) {
                        case 'int': $criteriaValue = (int)$criteriaValue; break;
                        case 'string': $criteriaValue = (string)$criteriaValue; break;
                        case 'bool': $criteriaValue = (bool)$criteriaValue; break;
                        case 'array': $criteriaValue = json_decode($criteriaValue, true); break;
                        case 'float': $criteriaValue = (double)$criteriaValue; break;
                        default: break;
                    }
                }
                foreach($items as $id => $item) {
                    if ('*empty*' === $criteriaValue) {
                        if (isset($item[$criteriaValue]) && strlen($item[$criteriaValue])) {
                            unset($items[$id]);
                            continue;
                        }
                        continue;
                    } elseif ('*notempty*' === $criteriaValue) {
                        if (!isset($item[$criteriaValue]) || !strlen($item[$criteriaValue])) {
                            unset($items[$id]);
                            continue;
                        }
                        continue;
                    } elseif ('$or' === $criteriaKey) {
                        foreach($criteriaValue as $cv) {
                            foreach($cv as $cc => $vv) {
                                if (isset($item[$cc]) && $item[$cc] === $vv) {
                                    continue 3;
                                }
                            }
                        }
                        unset($items[$id]);
                    }
                    if (!isset($item[$criteriaKey]) || ($item[$criteriaKey] !== $criteriaValue)) {
                        unset($items[$id]);
                        continue;
                    }
                    if ($eachCallback) $item = $eachCallback($item);
                    if (is_array($fields) && count($fields) > 0) {
                        $item = array_intersect_key($item, $keyFields);
                        $items[$id] = $item;
                    }
                }
            }
        }

        if (!$fieldFiltered) {
            foreach($items as $id => $item) {
                if ($eachCallback) $item = $eachCallback($item);
                if (is_array($fields) && count($fields) > 0) {
                    $item = array_intersect_key($item, $keyFields);
                    $items[$id] = $item;
                }
            }
        }

        return $this;
    }
    /**
     * @param array $items
     * @param int   $limit
     * @param int   $offset
     *
     * @return $this
     */
    protected function paginateItems(&$items, $limit, $offset)
    {
        if (is_numeric($offset) && $offset > 0) {
            if (is_numeric($limit) && $limit > 0) {
                $items = array_slice($items, $offset, $limit, true);
            } else {
                $items = array_slice($items, $offset, null, true);
            }
        } else {
            if (is_numeric($limit) && $limit > 0) {
                $items = array_slice($items, 0, $limit, true);
            }
        }

        return $this;
    }
    /**
     * @param array $items
     * @param array $sorts
     *
     * @return $this
     */
    protected function sortItems(&$items, $sorts = [])
    {
        if (!is_array($sorts)) $sorts = [];

        uasort($items, function ($a, $b) use ($sorts) {
            foreach($sorts as $field => $direction) {
                if (false === $direction || -1 === (int)$direction || 0 === (int)$direction || 'false' === $direction || null === $direction) {
                    if (!isset($a[$field])) {
                        if (!isset($b[$field])) {
                            continue;
                        } else {
                            return -1;
                        }
                    } elseif (!isset($b[$field])) {
                        continue;
                    }
                    $result = strcmp($b[$field], $a[$field]);

                    if ($result > 0) return $result;
                } else {
                    if (!isset($a[$field])) {
                        if (!isset($b[$field])) {
                            continue;
                        } else {
                            return 1;
                        }
                    } elseif (!isset($b[$field])) {
                        continue;
                    }
                    $result = strcmp($a[$field], $b[$field]);

                    if ($result > 0) return $result;
                }
            }

            return -1;
        });

        return $this;
    }
    /**
     * @param string   $parentId
     * @param array    $criteria
     * @param array    $fields
     * @param int|null $limit
     * @param int      $offset
     * @param array    $sorts
     * @param \Closure $eachCallback
     *
     * @return array
     */
    public function find(
        $parentId, $criteria = [], $fields = [], $limit = null, $offset = 0, $sorts = [], \Closure $eachCallback = null
    )
    {
        if (!$this->getRepository()->hasDocumentProperty($parentId, $this->getRepoKey())) return [];

        $items = $this->getRepository()->getDocumentProperty($parentId, $this->getRepoKey());

        if (!is_array($items) || !count($items)) return [];

        $this->sortItems($items, $sorts);
        $this->filterItems($items, $criteria, $fields, $eachCallback);
        $this->paginateItems($items, $limit, $offset);

        return $items;
    }
    /**
     * @param string   $parentId
     * @param array    $criteria
     * @param array    $fields
     * @param int|null $limit
     * @param int      $offset
     * @param array    $sorts
     * @param \Closure $eachCallback
     *
     * @return array
     */
    public function findWithTotal(
        $parentId, $criteria = [], $fields = [], $limit = null, $offset = 0, $sorts = [], \Closure $eachCallback = null
    )
    {
        return [
            $this->find($parentId, $criteria, $fields, $limit, $offset, $sorts, $eachCallback),
            $this->count($parentId, $criteria),
        ];
    }
    /**
     * @param string $parentId
     * @param string $id
     *
     * @return bool
     */
    public function has($parentId, $id)
    {
        return (bool)$this->getRepository()
            ->hasDocumentProperty($parentId, sprintf('%s.%s', $this->getRepoKey(), $id));
    }
    /**
     * @param string $parentId
     * @param string $id
     *
     * @return bool
     */
    public function hasNot($parentId, $id)
    {
        return !$this->has($parentId, $id);
    }
    /**
     * @param string $parentId
     * @param string $id
     *
     * @return array
     */
    public function get($parentId, $id)
    {
        return $this->getRepository()->getDocumentProperty($parentId, sprintf('%s.%s', $this->getRepoKey(), $id));
    }
    /**
     * @param string $parentId
     * @param string $id
     *
     * @return $this
     *
     * @throws \Exception
     */
    public function checkExist($parentId, $id)
    {
        if ($this->hasNot($parentId, $id))
            $this->throwException(
                404,
                "Unknown %s '%s' for %s '%s'", $this->getSubType(), $id, $this->getType(), $parentId
            );

        return $this;
    }
    /**
     * @param string $parentId
     * @param string $id
     *
     * @return $this
     */
    public function checkNotExist($parentId, $id)
    {
        if ($this->has($parentId, $id))
            $this->throwException(
                403,
                "%s '%s' already exist for %s '%s'", ucfirst($this->getSubType()), $id, $this->getType(), $parentId
            );

        return $this;
    }
    /**
     * @param string $parentId
     * @param array  $criteria
     *
     * @return $this
     *
     * @todo use criteria (NYI)
     */
    public function purge($parentId, $criteria = [])
    {
        $ids = array_keys($this->find($parentId));

        $this->getRepository()->setDocumentProperty($parentId, $this->getRepoKey(), (object)[]);

        unset($criteria);

        return $this->event($parentId, 'purged', ['ids' => $ids]);
    }
    /**
     * @param string $parentId
     * @param string $id
     *
     * @return $this
     *
     * @throws \Exception
     */
    public function delete($parentId, $id)
    {
        try {
            $deletedDoc = null;

            if ($this->observed('deleted.full')) {
                $deletedDoc = $this->get($parentId, $id);
            } else {
                $this->checkExist($parentId, $id);
            }

            $this->onDeleteBeforeSave($parentId, $id);

            $this->getRepository()->unsetDocumentProperty($parentId, sprintf('%s.%s', $this->getRepoKey(), $id));

            $this->onDeleteAfterSave($parentId, $id);

            $this->event($parentId, 'deleted', ['id' => $id]);

            if (null !== $deletedDoc) {
                $this->event($parentId, 'deleted.full', $deletedDoc);
            }

            return $this;
        } catch (\Exception $e) {
            if ($this->observed('delete.failed')) {
                $this->event($parentId, 'delete.failed', ['id' => $id, 'exception' => $e]);
            }
            throw $e;
        }
    }
    /**
     * @param string $parentId
     * @param array  $ids
     *
     * @return $this
     *
     * @throws \Exception
     */
    public function deleteBulk($parentId, $ids)
    {
        if (!is_array($ids)) {
            $this->throwException(412, "No list of Ids specified for deletion");
        }

        if (!count($ids)) {
            $this->throwException(412, "No ids to delete");
        }

        try {
            $deletedDocs = [];
            $properties  = [];

            $exceptions = [];

            foreach ($ids as $id) {
                $exceptions[$id] = [];
                try {
                    if ($this->observed('deleted.full')) {
                        $deletedDocs[$id] = $this->get($parentId, $id);
                    } else {
                        $this->checkExist($parentId, $id);
                    }

                    $this->onDeleteBeforeSave($parentId, $id);

                    $properties[] = sprintf('%s.%s', $this->getRepoKey(), $id);
                } catch (\Exception $e) {
                    $exceptions[$id][0] = $e;
                }
            }

            if (count($properties)) {
                $this->getRepository()->unsetDocumentProperty($parentId, $properties);

                foreach ($ids as $id) {
                    try {
                        $this->onDeleteAfterSave($parentId, $id);

                        $this->event($parentId, 'deleted', ['id' => $id]);

                        if (isset($deletedDocs[$id])) {
                            $this->event($parentId, 'deleted.full', $deletedDocs[$id]);
                        }
                    } catch (\Exception $e) {
                        $exceptions[$id][1] = $e;
                    }
                }
            }

            $messages = [];

            foreach($exceptions as $id => $exceptionList) {
                if (!count($exceptionList)) {
                    continue;
                }
                if ($this->observed('delete.failed')) {
                    $this->event($parentId, 'delete.failed', ['id' => $id, 'exception' => isset($exceptionList[0]) ? $exceptionList[0] : $exceptionList[1]]);
                }
                if (isset($exceptionList[0])) {
                    /** @var \Exception $exception */
                    $exception = $exceptionList[0];
                    $messages[] = $exception->getMessage();
                }
                if (isset($exceptionList[1])) {
                    /** @var \Exception $exception */
                    $exception = $exceptionList[1];
                    $messages[] = $exception->getMessage();
                }
            }

            if (count($messages)) {
                throw new \RuntimeException(join("\n", $messages), 500);
            }

            return $this;
        } catch (\Exception $e) {
            if ($this->observed('deleteBulk.failed')) {
                $this->event($parentId, 'deleteBulk.failed', ['ids' => $ids, 'exception' => $e]);
            }
            throw $e;
        }
    }
    /**
     * @param string     $parentId
     * @param array      $ids
     * @param array|null $data
     *
     * @return $this
     */
    public function updateBulk($parentId, $ids, $data = null)
    {
        if (!is_array($ids)) {
            $this->throwException(412, "No list of Ids specified for update");
        }

        if (!count($ids)) {
            $this->throwException(412, "No ids to update");
        }

        if (null !== $data) {
            $datas = array_fill_keys($ids, $data);
        } else {
            $datas = $ids;
        }

        $updatedDocs = [];
        $properties  = [];
        $unsaveds    = [];
        $updateDatas = [];
        $errors      = [];

        foreach($datas as $id => $data) {
            try {
                if ($this->observed('updated.fullWithOld')) {
                    $updatedDocs[$id] = $this->get($parentId, $id);
                }

                $updateDatas[$id] = $this->prepareUpdate($parentId, $id, $data);
                $unsaveds[$id] = null;

                if (isset($updateDatas[$id]['unsaved'])) {
                    $unsaveds[$id] = $updateDatas[$id]['unsaved'];
                    unset($updateDatas[$id]['unsaved']);
                }

                foreach ($updateDatas[$id] as $k => $v) {
                    $properties[sprintf('%s.%s', $id, $k)] = $v;
                }

                if (isset($unsaveds[$id])) {
                    $updateDatas[$id] += $unsaveds[$id];
                }
            } catch (\Exception $e) {
                $errors[$id] = $e;
            }
        }

        $this->setValues($parentId, $properties);

        foreach(array_keys($updateDatas) as $id) {
            $this->event($parentId, 'updated', ['id' => $id] + $updateDatas[$id]);

            $doc = $this->get($parentId, $id);

            if ($this->observed('updated.full')) $this->event($parentId, 'updated.full', $doc + (isset($unsaveds[$id]) ? $unsaveds[$id] : []));

            if (isset($updatedDocs[$id])) {
                $this->event($parentId, 'updated.fullWithOld', ['old' => $updatedDocs[$id]] + $doc + (isset($unsaveds[$id]) ? $unsaveds[$id] : []));
            }
        }

        if (count($errors)) $this->throwImportException($errors);

        return $this;
    }
    /**
     * @param string $parentId
     * @param string $id
     * @param array $data
     *
     * @return array
     */
    public function prepareUpdate($parentId, $id, $data)
    {
        $options = [];

        if (method_exists($this, 'onUpdateConvert')) $options['convert'] = [$this, 'onUpdateConvert'];

        $data = $this->onUpdateBeforeValidate($parentId, $id, $data, $options);

        $data = $this->validate($data, [], 'update', false, null, $options);

        $this->checkExist($parentId, $id);

        $data = $this->onUpdateBeforeSave($parentId, $id, $data);

        return $data;
    }
    /**
     * @param string $parentId
     * @param string $id
     * @param array  $data
     *
     * @return $this
     */
    public function update($parentId, $id, $data)
    {
        $old = null;

        if ($this->observed('updated.fullWithOld')) {
            $old = $this->get($parentId, $id);
        }

        $data = $this->prepareUpdate($parentId, $id, $data);

        $unsaved = null;

        if (isset($data['unsaved'])) {
            $unsaved = $data['unsaved'];
            unset($data['unsaved']);
        }

        $this->set($parentId, $id, $data);

        if (isset($unsaved)) {
            $data += $unsaved;
        }

        $data = $this->onUpdateAfterSave($parentId, $id, $data);

        $this->event($parentId, 'updated', ['id' => $id] + $data);

        $doc = $this->get($parentId, $id);

        if ($this->observed('updated.full')) $this->event($parentId, 'updated.full', $doc + (isset($unsaved) ? $unsaved : []));

        if (null !== $old) {
            $this->event($parentId, 'updated.fullWithOld', ['old' => $old] + $doc + (isset($unsaved) ? $unsaved : []));
        }

        return $this;
    }
    /**
     * @param string $parentId
     * @param string $id
     * @param mixed  $data
     *
     * @return $this
     */
    protected function set($parentId, $id, $data)
    {
        $this->getRepository()->setDocumentProperty(
            $parentId,
            sprintf('%s.%s', $this->getRepoKey(), $id) ,
            array_merge(
                $this->getRepository()->getDocumentProperty($parentId, sprintf('%s.%s', $this->getRepoKey(), $id)),
                $data
            )
        );

        return $this;
    }
    /**
     * @param string $parentId
     * @param string $id
     * @param mixed  $value
     *
     * @return $this
     */
    protected function setValue($parentId, $id, $value)
    {
        $this->getRepository()->setDocumentProperty(
            $parentId, sprintf('%s.%s', $this->getRepoKey(), $id) , $value
        );

        return $this;
    }
    /**
     * @param string $parentId
     * @param array  $values
     *
     * @return $this
     */
    protected function setValues($parentId, $values)
    {
        $that = $this;

        $values = array_combine(
            array_map(
                function ($a) use ($that, $parentId) {
                    return sprintf('%s.%s', $that->getRepoKey(), $a);
                },
                array_keys($values)
            ),
            array_values($values)
        );

        $this->getRepository()->setDocumentProperties($parentId, $values);

        return $this;
    }
    /**
     * @param string       $parentId
     * @param string|array $id
     * @param int          $value
     *
     * @return $this
     */
    public function increment($parentId, $id, $value = 1)
    {
        $originalId = $id;

        if (true === is_array($id)) {
            foreach ($id as $k => $v) {
                unset($id[$k]);
                $id[sprintf('%s.%s', $this->getRepoKey(), $k)] = $v;
            }
            $values = $id;
            unset($id);
            $this->getRepository()->incrementMultipleDocumentProperties($parentId, $values);

            return $this->event($parentId, 'updated', ['id' => array_keys($originalId)] + $this->getRepository()->getDocumentProperty($parentId, $this->getRepoKey()));
        } else {
            $this->getRepository()
                ->incrementDocumentProperty($parentId, sprintf('%s.%s', $this->getRepoKey(), $id), $value);
            return $this->event($parentId, 'updated', ['id' => $id] + [$id => $this->getRepository()->getDocumentProperty($parentId, sprintf('%s.%s', $this->getRepoKey(), $id))]);
        }

    }
    /**
     * @param string       $parentId
     * @param string|array $id
     * @param int          $value
     *
     * @return $this
     */
    public function decrement($parentId, $id, $value = 1)
    {
        if (!is_array($id)) return $this->increment($parentId, $id, -$value);

        return $this->increment(
            $parentId,
            array_combine(array_keys($id), array_map(function ($v) { return -$v;}, $id))
        );
    }
    /**
     * @param string $parentId
     * @param array  $data
     *
     * @return array
     */
    public function replaceBulk($parentId, $data)
    {
        $this->getRepository()->setDocumentProperty($parentId, $this->getRepoKey(), (object)[]);

        $this->event($parentId, 'emptied');

        return $this->createBulk($parentId, $data);
    }
}