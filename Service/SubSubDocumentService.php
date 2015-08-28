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
use Velocity\Bundle\ApiBundle\Event;
use Velocity\Bundle\ApiBundle\Traits\ModelServiceTrait;

/**
 * Sub Sub Document Service.
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
class SubSubDocumentService implements SubSubDocumentServiceInterface
{
    use ModelServiceTrait;
    /**
     * @return int
     */
    public function getExpectedTypeCount()
    {
        return 3;
    }
    /**
     * Create a new document.
     *
     * @param string $pParentId
     * @param string $parentId
     * @param mixed  $data
     * @param array  $options
     *
     * @return mixed
     */
    public function create($pParentId, $parentId, $data, $options = [])
    {
        list($doc, $array) = $this->prepareCreate($pParentId, $parentId, $data, $options);

        unset($data);

        if (!isset($doc->id)) {
            throw $this->createException(412, 'Empty id for sub document model');
        }

        $this->getRepository()->setHashProperty($pParentId, $this->getRepoKey([$parentId, $doc->id]), $array);

        return $this->completeCreate($pParentId, $parentId, $doc, $array, $options);
    }
    /**
     * Create document if not exist or update it.
     *
     * @param string $pParentId
     * @param string $parentId
     * @param mixed  $data
     * @param array  $options
     *
     * @return mixed
     */
    public function createOrUpdate($pParentId, $parentId, $data, $options = [])
    {
        if (isset($data['id']) && $this->has($pParentId, $parentId, $data['id'])) {
            $id = $data['id'];
            unset($data['id']);

            return $this->update($pParentId, $parentId, $id, $data, $options);
        }

        return $this->create($pParentId, $parentId, $data, $options);
    }
    /**
     * Create a list of documents.
     *
     * @param string $pParentId
     * @param string $parentId
     * @param mixed  $bulkData
     * @param array  $options
     *
     * @return mixed
     */
    public function createBulk($pParentId, $parentId, $bulkData, $options = [])
    {
        $this->checkBulkData($bulkData, $options);

        $docs   = [];
        $arrays = [];

        foreach ($bulkData as $i => $data) {
            list($docs[$i], $array) = $this->prepareCreate($pParentId, $parentId, $data, $options);
            if (!isset($docs[$i]->id)) {
                throw $this->createException(412, 'Empty id for sub document model');
            }
            $arrays[$this->mutateKeyToRepoChangesKey('', [$pParentId, $docs[$i]->id])] = $array;
            unset($bulkData[$i]);
        }

        foreach ($this->getRepository()->setProperties($pParentId, $arrays, $options) as $i => $array) {
            $this->completeCreate($pParentId, $parentId, $docs[$i], $array, $options);
        }

        unset($arrays);

        return $docs;
    }
    /**
     * Create documents if not exist or update them.
     *
     * @param string $pParentId
     * @param string $parentId
     * @param mixed  $bulkData
     * @param array  $options
     *
     * @return mixed
     */
    public function createOrUpdateBulk($pParentId, $parentId, $bulkData, $options = [])
    {
        $this->checkBulkData($bulkData, $options);

        $toCreate = [];
        $toUpdate = [];

        foreach ($bulkData as $i => $data) {
            unset($bulkData[$i]);
            if (isset($data['id']) && $this->has($pParentId, $parentId, $data['id'])) {
                $toUpdate[$i] = $data;
            } else {
                $toCreate[$i] = $data;
            }
        }

        unset($bulkData);

        $docs = [];

        if (count($toCreate)) {
            $docs += $this->createBulk($pParentId, $parentId, $toCreate, $options);
        }

        unset($toCreate);

        if (count($toUpdate)) {
            $docs += $this->updateBulk($pParentId, $parentId, $toUpdate, $options);
        }

        unset($toUpdate);

        return $docs;
    }
    /**
     * Create documents if not exist or delete them.
     *
     * @param string $pParentId
     * @param string $parentId
     * @param mixed  $bulkData
     * @param array  $options
     *
     * @return mixed
     */
    public function createOrDeleteBulk($pParentId, $parentId, $bulkData, $options = [])
    {
        $this->checkBulkData($bulkData, $options);

        $toCreate = [];
        $toDelete = [];

        foreach ($bulkData as $i => $data) {
            if (isset($data['id']) && $this->has($pParentId, $parentId, $data['id'])) {
                $toDelete[$i] = $data;
            } else {
                $toCreate[$i] = $data;
            }
            unset($bulkData[$i]);
        }

        unset($bulkData);

        $docs = [];

        if (count($toCreate)) {
            $docs += $this->createBulk($pParentId, $parentId, $toCreate, $options);
        }

        unset($toCreate);

        if (count($toDelete)) {
            $docs += $this->deleteBulk($pParentId, $parentId, $toDelete, $options);
        }

        unset($toDelete);

        return $docs;
    }
    /**
     * Count documents matching the specified criteria.
     *
     * @param string $pParentId
     * @param string $parentId
     * @param mixed  $criteria
     * @param array  $options
     *
     * @return mixed
     */
    public function count($pParentId, $parentId, $criteria = [], $options = [])
    {
        if (!$this->getRepository()->hasProperty($pParentId, $this->getRepoKey([$parentId]), $options)) {
            return 0;
        }

        $items = $this->getRepository()->getListProperty($pParentId, $this->getRepoKey($parentId), $options);

        $this->filterItems($items, $criteria);

        unset($criteria);

        return count($items);
    }
    /**
     * Retrieve the documents matching the specified criteria.
     *
     * @param string   $pParentId
     * @param string   $parentId
     * @param array    $criteria
     * @param array    $fields
     * @param null|int $limit
     * @param int      $offset
     * @param array    $sorts
     * @param array    $options
     *
     * @return mixed
     */
    public function find($pParentId, $parentId, $criteria = [], $fields = [], $limit = null, $offset = 0, $sorts = [], $options = [])
    {
        if (!$this->getRepository()->hasProperty($pParentId, $this->getRepoKey([$parentId]))) {
            return [];
        }

        $items = $this->getRepository()->getListProperty($pParentId, $this->getRepoKey([$parentId]));

        $this->sortItems($items, $sorts, $options);
        $this->filterItems($items, $criteria, $fields, $options);
        $this->paginateItems($items, $limit, $offset, $options);

        foreach ($items as $k => $v) {
            $items[$k] = $this->callback($parentId, 'fetched', $this->convertToModel($v, $options), $options);
        }

        return $items;
    }
    /**
     * Retrieve the documents matching the specified criteria and return a page with total count.
     *
     * @param string   $pParentId
     * @param string   $parentId
     * @param array    $criteria
     * @param array    $fields
     * @param null|int $limit
     * @param int      $offset
     * @param array    $sorts
     * @param array    $options
     *
     * @return mixed
     */
    public function findWithTotal($pParentId, $parentId, $criteria = [], $fields = [], $limit = null, $offset = 0, $sorts = [], $options = [])
    {
        return [
            $this->find($pParentId, $parentId, $criteria, $fields, $limit, $offset, $sorts, $options),
            $this->count($pParentId, $parentId, $criteria, $options),
        ];
    }
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
    public function getProperty($pParentId, $parentId, $id, $property, $options = [])
    {
        return $this->getRepository()->getProperty($pParentId, $this->getRepoKey([$parentId, $id, $property]), $options);
    }
    /**
     * Test if specified document exist.
     *
     * @param string $pParentId
     * @param string $parentId
     * @param mixed  $id
     * @param array  $options
     *
     * @return bool
     */
    public function has($pParentId, $parentId, $id, $options = [])
    {
        return $this->getRepository()->hasProperty($pParentId, $this->getRepoKey([$parentId, $id]), $options);
    }
    /**
     * Test if specified document exist by specified field.
     *
     * @param string $pParentId
     * @param string $parentId
     * @param string $fieldName
     * @param mixed  $fieldValue
     * @param array  $options
     *
     * @return bool
     */
    public function hasBy($pParentId, $parentId, $fieldName, $fieldValue, $options = [])
    {
        return 0 < count($this->find($pParentId, $parentId, [$fieldName => $fieldValue], 1, 0, $options));
    }
    /**
     * Test if specified document does not exist.
     *
     * @param string $pParentId
     * @param string $parentId
     * @param mixed  $id
     * @param array  $options
     *
     * @return bool
     */
    public function hasNot($pParentId, $parentId, $id, $options = [])
    {
        return !$this->has($pParentId, $parentId, $id, $options);
    }
    /**
     * Return the specified document.
     *
     * @param string $pParentId
     * @param string $parentId
     * @param mixed  $id
     * @param array  $fields
     * @param array  $options
     *
     * @return mixed
     */
    public function get($pParentId, $parentId, $id, $fields = [], $options = [])
    {
        return $this->callback(
            $pParentId,
            'fetched',
            $this->convertToModel(
                $this->getRepository()->getHashProperty($pParentId, $this->getRepoKey([$parentId, $id], $options), $fields, $options),
                $options
            ),
            $options
        );
    }
    /**
     * Purge all the documents matching the specified criteria.
     *
     * @param string $pParentId
     * @param string $parentId
     * @param array  $criteria
     * @param array  $options
     *
     * @return mixed
     */
    public function purge($pParentId, $parentId, $criteria = [], $options = [])
    {
        if ([] !== $criteria) {
            throw $this->createException(500, 'Purging sub sub documents with criteria not supported');
        }


        $this->callback($pParentId, $parentId, 'pre_purge', [], $options);

        $this->getRepository()->resetListProperty($pParentId, $this->getRepoKey([$parentId]), $options);

        $this->callback($pParentId, $parentId, 'purged', $criteria, $options);
        $this->event($pParentId, $parentId, 'purged');

        unset($options);
        unset($criteria);

        return $this;
    }
    /**
     * Delete the specified document.
     *
     * @param string $pParentId
     * @param string $parentId
     * @param mixed  $id
     * @param array  $options
     *
     * @return mixed
     *
     * @throws Exception
     */
    public function delete($pParentId, $parentId, $id, $options = [])
    {
        list($old) = $this->prepareDelete($pParentId, $parentId, $id, $options);

        $this->getRepository()->unsetProperty($pParentId, $this->getRepoKey([$parentId, $id]), $options);

        return $this->completeDelete($pParentId, $parentId, $id, $old, $options);
    }
    /**
     * Delete the specified documents.
     *
     * @param string $pParentId
     * @param string $parentId
     * @param array  $ids
     * @param array  $options
     *
     * @return mixed
     */
    public function deleteBulk($pParentId, $parentId, $ids, $options = [])
    {
        $this->checkBulkData($ids, $options);

        $olds     = [];
        $deleteds = [];

        foreach ($ids as $id) {
            list($olds[$id])  = $this->prepareDelete($pParentId, $parentId, $id, $options);
            $deleteds[$id] = $this->getRepoKey([$parentId, $id]);
        }

        if (count($deleteds)) {
            $this->getRepository()->unsetProperty($pParentId, array_values($deleteds), $options);
        }

        foreach ($ids as $id) {
            $deleteds[$id] = $this->completeDelete($pParentId, $parentId, $id, $olds[$id], $options);
            unset($olds[$id], $ids[$id]);
        }

        unset($ods, $ids);

        return $deleteds;
    }
    /**
     * @param string $pParentId
     * @param string $parentId
     * @param string $id
     * @param array  $data
     * @param array  $options
     *
     * @return $this
     */
    public function update($pParentId, $parentId, $id, $data, $options = [])
    {
        list($doc, $array, $old) = $this->prepareUpdate($pParentId, $parentId, $id, $data, $options);

        unset($data);

        $this->getRepository()->setProperties($pParentId, $this->mutateArrayToRepoChanges($array, [$parentId, $id]), $options);

        return $this->completeUpdate($pParentId, $parentId, $id, $doc, $array, $old, $options);
    }
    /**
     * @param mixed $pParentId
     * @param mixed $parentId
     * @param mixed $fieldName
     * @param mixed $fieldValue
     * @param array $data
     * @param array $options
     *
     * @return $this
     */
    public function updateBy($pParentId, $parentId, $fieldName, $fieldValue, $data, $options = [])
    {
        $docs = $this->find($pParentId, $parentId, [$fieldName => $fieldValue], ['id'], 1, 0, $options);

        if (!count($docs)) {
            throw $this->createException(404, "Unknown %s with %s '%s' (%s - %s)", join(' ', $this->getTypes()), $fieldName, $fieldValue, $pParentId, $parentId);
        }

        return $this->update($pParentId, $parentId, array_shift($docs)->id, $data, $options);
    }
    /**
     * @param string $pParentId
     * @param string $parentId
     * @param array  $bulkData
     * @param array  $options
     *
     * @return $this
     */
    public function updateBulk($pParentId, $parentId, $bulkData, $options = [])
    {
        $this->checkBulkData($bulkData, $options);

        $docs    = [];
        $changes = [];
        $olds    = [];
        $arrays  = [];

        foreach ($bulkData as $i => $data) {
            $id = $data['id'];
            unset($data['id']);
            list($docs[$i], $arrays[$i], $olds[$i]) = $this->prepareUpdate($pParentId, $parentId, $id, $data, $options);
            unset($bulkData[$i]);
            $changes += $this->mutateArrayToRepoChanges($arrays[$i], [$parentId, $id]);
        }

        $this->getRepository()->setProperties($pParentId, $changes, $options);

        foreach ($arrays as $i => $array) {
            $this->completeUpdate($pParentId, $parentId, $i, $docs[$i], $array, $olds[$i], $options);
            unset($arrays[$array], $olds[$i]);
        }

        unset($olds);
        unset($arrays);

        return $docs;
    }
    /**
     * Replace all the specified documents.
     *
     * @param string $pParentId
     * @param string $parentId
     * @param array  $data
     * @param array  $options
     *
     * @return mixed
     */
    public function replaceAll($pParentId, $parentId, $data, $options = [])
    {
        $this->callback($pParentId, $parentId, 'pre_empty', []);

        $this->getRepository()->resetListProperty($pParentId, $this->getRepoKey([$parentId]), $options);

        $this->callback($pParentId, $parentId, 'emptied', []);
        $this->event($pParentId, $parentId, 'emptied');

        return $this->createBulk($pParentId, $parentId, $data, $options);
    }
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
    public function replaceBulk($pParentId, $parentId, $bulkData, $options = [])
    {
        $this->checkBulkData($bulkData, $options);

        $ids = [];

        foreach ($bulkData as $k => $v) {
            if (!isset($v['id'])) {
                throw $this->createException(412, 'Missing id for item #%s', $k);
            }
            $ids[] = $v['id'];
        }

        $this->deleteBulk($pParentId, $parentId, $ids, $options);

        unset($ids);

        return $this->createBulk($pParentId, $parentId, $bulkData, $options);
    }
    /**
     * Check if specified document exist.
     *
     * @param string $pParentId
     * @param string $parentId
     * @param mixed  $id
     * @param array  $options
     *
     * @return $this
     *
     * @throws Exception
     */
    public function checkExist($pParentId, $parentId, $id, $options = [])
    {
        $this->getRepository()->checkPropertyExist($pParentId, $this->getRepoKey([$parentId, $id], $options), $options);

        return $this;
    }
    /**
     * Check is specified document does not exist.
     *
     * @param string $pParentId
     * @param string $parentId
     * @param mixed  $id
     * @param array  $options
     *
     * @return $this
     *
     * @throws Exception
     */
    public function checkNotExist($pParentId, $parentId, $id, $options = [])
    {
        $this->getRepository()->checkPropertyNotExist($pParentId, $this->getRepoKey([$parentId, $id], $options), $options);

        return $this;
    }
    /**
     * Increment the specified property of the specified document.
     *
     * @param string       $pParentId
     * @param string       $parentId
     * @param mixed        $id
     * @param string|array $property
     * @param int          $value
     * @param array        $options
     *
     * @return $this
     */
    public function increment($pParentId, $parentId, $id, $property, $value = 1, $options = [])
    {
        $this->callback($pParentId, $parentId, 'pre_increment', ['id' => $id, 'value' => $value, 'property' => $property]);
        $this->callback($pParentId, $parentId, 'pre_increment.'.$property, ['id' => $id, 'value' => $value]);

        $this->getRepository()->incrementProperty($pParentId, $this->getRepoKey([$parentId, $id, $property]), $value, $options);

        $this->callback($pParentId, $parentId, 'incremented', ['id' => $id, 'value' => $value, 'property' => $property]);
        $this->callback($pParentId, $parentId, 'incremented.'.$property, ['id' => $id, 'value' => $value]);
        $this->event($pParentId, $parentId, 'incremented', ['id' => $id, 'value' => $value, 'property' => $property]);
        $this->event($pParentId, $parentId, 'incremented.'.$property, ['id' => $id, 'value' => $value]);

        return $this;
    }
    /**
     * Decrement the specified property of the specified document.
     *
     * @param string       $pParentId
     * @param string       $parentId
     * @param mixed        $id
     * @param string|array $property
     * @param int          $value
     * @param array        $options
     *
     * @return $this
     */
    public function decrement($pParentId, $parentId, $id, $property, $value = 1, $options = [])
    {
        $this->callback($pParentId, $parentId, 'pre_decrement', ['id' => $id, 'value' => $value, 'property' => $property]);
        $this->callback($pParentId, $parentId, 'pre_decrement.'.$property, ['id' => $id, 'value' => $value]);

        $this->getRepository()->decrementProperty($pParentId, $this->getRepoKey([$parentId, $id, $property]), $value, $options);

        $this->callback($pParentId, $parentId, 'decremented', ['id' => $id, 'value' => $value, 'property' => $property]);
        $this->callback($pParentId, $parentId, 'decremented.'.$property, ['id' => $id, 'value' => $value]);
        $this->event($pParentId, $parentId, 'decremented', ['id' => $id, 'value' => $value, 'property' => $property]);
        $this->event($pParentId, $parentId, 'decremented.'.$property, ['id' => $id, 'value' => $value]);

        return $this;
    }
    /**
     * Trigger the specified document event if listener are registered.
     *
     * @param string $pParentId
     * @param string $parentId
     * @param string $event
     * @param mixed  $data
     *
     * @return $this
     */
    protected function event($pParentId, $parentId, $event, $data = null)
    {
        return $this->dispatch(
            $this->buildEventName($event),
            new Event\DocumentEvent($data, $this->buildTypeVars([$pParentId, $parentId]))
        );
    }
    /**
     * Execute the registered callback and return the updated subject.
     *
     * @param string $pParentId
     * @param string $parentId
     * @param string $key
     * @param mixed  $subject
     * @param array  $options
     *
     * @return mixed
     */
    protected function callback($pParentId, $parentId, $key, $subject, $options = [])
    {
        return $this->getMetaDataService()->callback(
            $this->buildEventName($key),
            $subject,
            $this->buildTypeVars([$pParentId, $parentId]) + $options
        );
    }
    /**
     * @param string $pParentId
     * @param string $parentId
     * @param array  $data
     * @param array  $options
     *
     * @return array
     */
    protected function prepareCreate($pParentId, $parentId, $data, $options = [])
    {
        $data = $this->callback($pParentId, $parentId, 'create.pre_validate', $data, $options);
        $doc  = $this->validateData($data, 'create', $options);

        unset($data);

        $doc = $this->callback($pParentId, $parentId, 'create.validated', $doc, $options);
        $doc = $this->refreshModel($doc, $options);
        $doc = $this->callback($pParentId, $parentId, 'pre_save', $doc, $options);
        $doc = $this->callback($pParentId, $parentId, 'create.pre_save', $doc, $options);

        $this->checkBusinessRules($pParentId, $parentId, 'create', $doc, $options);

        $doc   = $this->callback($pParentId, $parentId, 'create.pre_save_checked', $doc, $options);
        $array = $this->convertToArray($doc, $options);
        $array = $this->callback($pParentId, $parentId, 'create.pre_save_array', $array, $options);

        return [$doc, $array];
    }
    /**
     * @param string $pParentId
     * @param string $parentId
     * @param mixed  $doc
     * @param mixed  $array
     * @param array  $options
     *
     * @return mixed
     */
    protected function completeCreate($pParentId, $parentId, $doc, $array, $options = [])
    {
        $array = $this->callback($pParentId, $parentId, 'create.saved_array', $array, $options);

        $doc->id = (string) $array['_id'];

        $doc = $this->callback($pParentId, $parentId, 'create.saved', $doc, $options);
        $doc = $this->callback($pParentId, $parentId, 'saved', $doc, $options);
        $doc = $this->callback($pParentId, $parentId, 'created', $doc, $options);

        $this->event($pParentId, $parentId, 'created', $doc);

        return $doc;
    }
    /**
     * @param string $pParentId
     * @param string $parentId
     * @param mixed  $id
     * @param array  $data
     * @param array  $options
     *
     * @return array
     */
    protected function prepareUpdate($pParentId, $parentId, $id, $data = [], $options = [])
    {
        $old = $this->get($pParentId, $parentId, $id, array_keys($data), $options);

        $data = $this->callback($pParentId, $parentId, 'update.pre_validate', $data, $options);
        $doc  = $this->validateData($data, 'update', ['clearMissing' => false] + $options);

        unset($data);

        $doc = $this->callback($pParentId, $parentId, 'update.validated', $doc, $options);
        $doc = $this->refreshModel($doc, $options);
        $doc = $this->callback($pParentId, $parentId, 'pre_save', $doc, $options);
        $doc = $this->callback($pParentId, $parentId, 'update.pre_save', $doc, $options);

        $this->checkBusinessRules($pParentId, $parentId, 'update', $doc, $options);

        $doc   = $this->callback($pParentId, $parentId, 'update.pre_save_checked', $doc, $options);
        $array = $this->convertToArray($doc, $options);
        $array = $this->callback($pParentId, $parentId, 'update.pre_save_array', $array, $options);

        return [$doc, $array, $old];
    }
    /**
     * @param string $pParentId
     * @param string $parentId
     * @param mixed  $id
     * @param mixed  $doc
     * @param array  $array
     * @param mixed  $old
     * @param array  $options
     *
     * @return mixed
     */
    protected function completeUpdate($pParentId, $parentId, $id, $doc, $array, $old, $options = [])
    {
        $this->callback($pParentId, $parentId, 'update.saved_array', $array, $options);

        unset($array);

        $doc = $this->callback($pParentId, $parentId, 'update.saved', $doc, $options);
        $doc = $this->callback($pParentId, $parentId, 'saved', $doc, $options);
        $doc = $this->callback($pParentId, $parentId, 'updated', $doc, $options);

        $this->event($pParentId, $parentId, 'updated', $doc);
        $this->event($pParentId, $parentId, 'updated_old', ['new' => $doc, 'old' => $old]);

        if ($this->observed('updated_full') || $this->observed('updated_full_old')) {
            $full = $this->get($pParentId, $parentId, $id, [], $options);
            $this->event($pParentId, $parentId, 'updated_full', $doc);
            $this->event($pParentId, $parentId, 'updated_full_old', ['new' => $full, 'old' => $old]);
            unset($full);
        }

        return $doc;
    }
    /**
     * @param string $pParentId
     * @param string $parentId
     * @param mixed  $id
     * @param array  $options
     *
     * @return array
     */
    protected function prepareDelete($pParentId, $parentId, $id, $options = [])
    {
        $old = $this->get($pParentId, $parentId, $id, [], $options);

        $this->callback($pParentId, $parentId, 'delete.pre_save', $old, $options);

        $this->checkBusinessRules($pParentId, $parentId, 'delete', $old, $options);

        $this->callback($pParentId, $parentId, 'delete.pre_save_checked', $old, $options);

        return [$old];
    }
    /**
     * @param string $pParentId
     * @param string $parentId
     * @param mixed  $id
     * @param mixed  $old
     * @param array  $options
     *
     * @return mixed
     */
    protected function completeDelete($pParentId, $parentId, $id, $old, $options = [])
    {
        $this->callback($pParentId, $parentId, 'delete.saved', ['id' => $id, 'old' => $old], $options);
        $this->callback($pParentId, $parentId, 'deleted', ['id' => $id, 'old' => $old], $options);

        $this->event($pParentId, $parentId, 'deleted', $id);
        $this->event($pParentId, $parentId, 'deleted_old', $old);

        return $old;
    }
    /**
     * @param string $pParentId
     * @param string $parentId
     * @param string $operation
     * @param mixed  $model
     * @param array  $options
     *
     * @return $this
     */
    protected function checkBusinessRules($pParentId, $parentId, $operation, $model, array $options = [])
    {
        $this->getBusinessRuleService()->executeBusinessRulesForModelOperation(
            $this->getModelName(),
            $operation,
            $model,
            $this->buildTypeVars([$pParentId, $parentId]) + $options
        );

        return $this;
    }
}
