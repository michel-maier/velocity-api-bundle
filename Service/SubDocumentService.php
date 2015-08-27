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
use Velocity\Bundle\ApiBundle\Traits\ModelServiceTrait;

/**
 * Sub Document Service.
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
class SubDocumentService implements SubDocumentServiceInterface
{
    use ModelServiceTrait;
    /**
     * @return int
     */
    public function getExpectedTypeCount()
    {
        return 2;
    }
    /**
     * Create a new document.
     *
     * @param string $parentId
     * @param mixed  $data
     * @param array  $options
     *
     * @return mixed
     */
    public function create($parentId, $data, $options = [])
    {
        list($doc, $array) = $this->prepareCreate($parentId, $data, $options);

        unset($data);

        if (!isset($doc->id)) {
            throw $this->createException(412, 'Empty id for sub document model');
        }

        $this->getRepository()->setHashProperty($parentId, $this->getRepoKey([$doc->id]), $array);

        return $this->completeCreate($parentId, $doc, $array, $options);
    }
    /**
     * Create document if not exist or update it.
     *
     * @param string $parentId
     * @param mixed  $data
     * @param array  $options
     *
     * @return mixed
     */
    public function createOrUpdate($parentId, $data, $options = [])
    {
        if (isset($data['id']) && $this->has($parentId, $data['id'])) {
            $id = $data['id'];
            unset($data['id']);

            return $this->update($parentId, $id, $data, $options);
        }

        return $this->create($parentId, $data, $options);
    }
    /**
     * Create a list of documents.
     *
     * @param string $parentId
     * @param mixed  $bulkData
     * @param array  $options
     *
     * @return mixed
     */
    public function createBulk($parentId, $bulkData, $options = [])
    {
        $this->checkBulkData($bulkData, $options);

        $docs   = [];
        $arrays = [];

        foreach ($bulkData as $i => $data) {
            list($docs[$i], $array) = $this->prepareCreate($parentId, $data, $options);
            if (!isset($docs[$i]->id)) {
                throw $this->createException(412, 'Empty id for sub document model');
            }
            $arrays[$this->mutateKeyToRepoChangesKey('', [$docs[$i]->id])] = $array;
            unset($bulkData[$i]);
        }

        foreach ($this->getRepository()->setProperties($parentId, $arrays, $options) as $i => $array) {
            $this->completeCreate($parentId, $docs[$i], $array, $options);
        }

        unset($arrays);

        return $docs;
    }
    /**
     * Create documents if not exist or update them.
     *
     * @param string $parentId
     * @param mixed  $bulkData
     * @param array  $options
     *
     * @return mixed
     */
    public function createOrUpdateBulk($parentId, $bulkData, $options = [])
    {
        $this->checkBulkData($bulkData, $options);

        $toCreate = [];
        $toUpdate = [];

        foreach ($bulkData as $i => $data) {
            unset($bulkData[$i]);
            if (isset($data['id']) && $this->has($parentId, $data['id'])) {
                $toUpdate[$i] = $data;
            } else {
                $toCreate[$i] = $data;
            }
        }

        unset($bulkData);

        $docs = [];

        if (count($toCreate)) {
            $docs += $this->createBulk($parentId, $toCreate, $options);
        }

        unset($toCreate);

        if (count($toUpdate)) {
            $docs += $this->updateBulk($parentId, $toUpdate, $options);
        }

        unset($toUpdate);

        return $docs;
    }
    /**
     * Create documents if not exist or delete them.
     *
     * @param string $parentId
     * @param mixed  $bulkData
     * @param array  $options
     *
     * @return mixed
     */
    public function createOrDeleteBulk($parentId, $bulkData, $options = [])
    {
        $this->checkBulkData($bulkData, $options);

        $toCreate = [];
        $toDelete = [];

        foreach ($bulkData as $i => $data) {
            if (isset($data['id']) && $this->has($parentId, $data['id'])) {
                $toDelete[$i] = $data;
            } else {
                $toCreate[$i] = $data;
            }
            unset($bulkData[$i]);
        }

        unset($bulkData);

        $docs = [];

        if (count($toCreate)) {
            $docs += $this->createBulk($parentId, $toCreate, $options);
        }

        unset($toCreate);

        if (count($toDelete)) {
            $docs += $this->deleteBulk($parentId, $toDelete, $options);
        }

        unset($toDelete);

        return $docs;
    }
    /**
     * Count documents matching the specified criteria.
     *
     * @param string $parentId
     * @param mixed  $criteria
     * @param array  $options
     *
     * @return mixed
     */
    public function count($parentId, $criteria = [], $options = [])
    {
        if (!$this->getRepository()->hasProperty($parentId, $this->getRepoKey(), $options)) {
            return 0;
        }

        $items = $this->getRepository()->getListProperty($parentId, $this->getRepoKey(), $options);

        $this->filterItems($items, $criteria);

        unset($criteria);

        return count($items);
    }
    /**
     * Retrieve the documents matching the specified criteria.
     *
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
    public function find($parentId, $criteria = [], $fields = [], $limit = null, $offset = 0, $sorts = [], $options = [])
    {
        if (!$this->getRepository()->hasProperty($parentId, $this->getRepoKey())) {
            return [];
        }

        $items = $this->getRepository()->getListProperty($parentId, $this->getRepoKey());

        $this->sortItems($items, $sorts, $options);
        $this->filterItems($items, $criteria, $fields, $options);
        $this->paginateItems($items, $limit, $offset, $options);

        foreach ($items as $k => $v) {
            $items[$k] = $this->callback('fetched', $this->convertToModel($v, $options), $options);
        }

        return $items;
    }
    /**
     * Retrieve the documents matching the specified criteria and return a page with total count.
     *
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
    public function findWithTotal($parentId, $criteria = [], $fields = [], $limit = null, $offset = 0, $sorts = [], $options = [])
    {
        return [
            $this->find($parentId, $criteria, $fields, $limit, $offset, $sorts, $options),
            $this->count($parentId, $criteria, $options),
        ];
    }
    /**
     * Return the property of the specified document.
     *
     * @param mixed  $parentId
     * @param mixed  $id
     * @param string $property
     * @param array  $options
     *
     * @return mixed
     */
    public function getProperty($parentId, $id, $property, $options = [])
    {
        return $this->getRepository()->getProperty($parentId, $this->getRepoKey([$id, $property]), $options);
    }
    /**
     * Test if specified document exist.
     *
     * @param string $parentId
     * @param mixed  $id
     * @param array  $options
     *
     * @return bool
     */
    public function has($parentId, $id, $options = [])
    {
        return $this->getRepository()->hasProperty($parentId, $this->getRepoKey([$id]), $options);
    }
    /**
     * Test if specified document exist by specified field.
     *
     * @param string $parentId
     * @param string $fieldName
     * @param mixed  $fieldValue
     * @param array  $options
     *
     * @return bool
     */
    public function hasBy($parentId, $fieldName, $fieldValue, $options = [])
    {
        return 0 < count($this->find($parentId, [$fieldName => $fieldValue], 1, 0, $options));
    }
    /**
     * Test if specified document does not exist.
     *
     * @param string $parentId
     * @param mixed  $id
     * @param array  $options
     *
     * @return bool
     */
    public function hasNot($parentId, $id, $options = [])
    {
        return !$this->has($parentId, $id, $options);
    }
    /**
     * Return the specified document.
     *
     * @param string $parentId
     * @param mixed  $id
     * @param array  $fields
     * @param array  $options
     *
     * @return mixed
     */
    public function get($parentId, $id, $fields = [], $options = [])
    {
        return $this->callback(
            'fetched',
            $this->convertToModel(
                $this->getRepository()->getHashProperty($parentId, $this->getRepoKey([$id], $options), $fields, $options),
                $options
            ),
            $options
        );
    }
    /**
     * Purge all the documents matching the specified criteria.
     *
     * @param string $parentId
     * @param array  $criteria
     * @param array  $options
     *
     * @return mixed
     */
    public function purge($parentId, $criteria = [], $options = [])
    {
        if ([] !== $criteria) {
            throw $this->createException(500, 'Purging sub documents with criteria not supported');
        }


        $this->callback($parentId, 'pre_purge', [], $options);

        $this->getRepository()->resetListProperty($parentId, $this->getRepoKey(), $options);

        $this->callback($parentId, 'purged', $criteria, $options);
        $this->event($parentId, 'purged');

        unset($options);
        unset($criteria);

        return $this;
    }
    /**
     * Delete the specified document.
     *
     * @param string $parentId
     * @param mixed  $id
     * @param array  $options
     *
     * @return mixed
     *
     * @throws Exception
     */
    public function delete($parentId, $id, $options = [])
    {
        list($old) = $this->prepareDelete($parentId, $id, $options);

        $this->getRepository()->unsetProperty($parentId, $this->getRepoKey([$id]), $options);

        return $this->completeDelete($parentId, $id, $old, $options);
    }
    /**
     * Delete the specified documents.
     *
     * @param string $parentId
     * @param array  $ids
     * @param array  $options
     *
     * @return mixed
     */
    public function deleteBulk($parentId, $ids, $options = [])
    {
        $this->checkBulkData($ids, $options);

        $olds     = [];
        $deleteds = [];

        foreach ($ids as $id) {
            list($olds[$id])  = $this->prepareDelete($parentId, $id, $options);
            $deleteds[$id] = $this->getRepoKey([$id]);
        }

        if (count($deleteds)) {
            $this->getRepository()->unsetProperty($parentId, array_values($deleteds), $options);
        }

        foreach ($ids as $id) {
            $deleteds[$id] = $this->completeDelete($parentId, $id, $olds[$id], $options);
            unset($olds[$id], $ids[$id]);
        }

        unset($ods, $ids);

        return $deleteds;
    }
    /**
     * @param string $parentId
     * @param string $id
     * @param array  $data
     * @param array  $options
     *
     * @return $this
     */
    public function update($parentId, $id, $data, $options = [])
    {
        list($doc, $array, $old) = $this->prepareUpdate($parentId, $id, $data, $options);

        unset($data);

        $this->getRepository()->setProperties($parentId, $this->mutateArrayToRepoChanges($array, [$id]), $options);

        return $this->completeUpdate($parentId, $id, $doc, $array, $old, $options);
    }
    /**
     * @param mixed $parentId
     * @param mixed $fieldName
     * @param mixed $fieldValue
     * @param array $data
     * @param array $options
     *
     * @return $this
     */
    public function updateBy($parentId, $fieldName, $fieldValue, $data, $options = [])
    {
        $docs = $this->find($parentId, [$fieldName => $fieldValue], ['id'], 1, 0, $options);

        if (!count($docs)) {
            throw $this->createException(404, "Unknown %s with %s '%s' (%s)", join(' ', $this->getTypes()), $fieldName, $fieldValue, $parentId);
        }

        return $this->update($parentId, array_shift($docs)->id, $data, $options);
    }
    /**
     * @param string $parentId
     * @param array  $bulkData
     * @param array  $options
     *
     * @return $this
     */
    public function updateBulk($parentId, $bulkData, $options = [])
    {
        $this->checkBulkData($bulkData, $options);

        $docs    = [];
        $changes = [];
        $olds    = [];
        $arrays  = [];

        foreach ($bulkData as $i => $data) {
            $id = $data['id'];
            unset($data['id']);
            list($docs[$i], $arrays[$i], $olds[$i]) = $this->prepareUpdate($parentId, $id, $data, $options);
            unset($bulkData[$i]);
            $changes += $this->mutateArrayToRepoChanges($arrays[$i], [$id]);
        }

        $this->getRepository()->setProperties($parentId, $changes, $options);

        foreach ($arrays as $i => $array) {
            $this->completeUpdate($parentId, $i, $docs[$i], $array, $olds[$i], $options);
            unset($arrays[$array], $olds[$i]);
        }

        unset($olds);
        unset($arrays);

        return $docs;
    }
    /**
     * Replace all the specified documents.
     *
     * @param string $parentId
     * @param array  $data
     * @param array  $options
     *
     * @return mixed
     */
    public function replaceAll($parentId, $data, $options = [])
    {
        $this->callback($parentId, 'pre_empty', []);

        $this->getRepository()->resetListProperty($parentId, $this->getRepoKey(), $options);

        $this->callback($parentId, 'emptied', []);
        $this->event($parentId, 'emptied');

        return $this->createBulk($parentId, $data, $options);
    }
    /**
     * Replace all the specified documents.
     *
     * @param mixed $parentId
     * @param array $bulkData
     * @param array $options
     *
     * @return mixed
     */
    public function replaceBulk($parentId, $bulkData, $options = [])
    {
        $this->checkBulkData($bulkData, $options);

        $ids = [];

        foreach ($bulkData as $k => $v) {
            if (!isset($v['id'])) {
                throw $this->createException(412, 'Missing id for item #%s', $k);
            }
            $ids[] = $v['id'];
        }

        $this->deleteBulk($parentId, $ids, $options);

        unset($ids);

        return $this->createBulk($parentId, $bulkData, $options);
    }
    /**
     * Check if specified document exist.
     *
     * @param string $parentId
     * @param mixed  $id
     * @param array  $options
     *
     * @return $this
     *
     * @throws Exception
     */
    public function checkExist($parentId, $id, $options = [])
    {
        $this->getRepository()->checkPropertyExist($parentId, $this->getRepoKey([$id], $options), $options);

        return $this;
    }
    /**
     * Check is specified document does not exist.
     *
     * @param string $parentId
     * @param mixed  $id
     * @param array  $options
     *
     * @return $this
     *
     * @throws Exception
     */
    public function checkNotExist($parentId, $id, $options = [])
    {
        $this->getRepository()->checkPropertyNotExist($parentId, $this->getRepoKey([$id], $options), $options);

        return $this;
    }
    /**
     * Increment the specified property of the specified document.
     *
     * @param string       $parentId
     * @param mixed        $id
     * @param string|array $property
     * @param int          $value
     * @param array        $options
     *
     * @return $this
     */
    public function increment($parentId, $id, $property, $value = 1, $options = [])
    {
        $this->callback($parentId, 'pre_increment', ['id' => $id, 'value' => $value, 'property' => $property]);
        $this->callback($parentId, 'pre_increment.'.$property, ['id' => $id, 'value' => $value]);

        $this->getRepository()->incrementProperty($parentId, $this->getRepoKey([$id, $property]), $value, $options);

        $this->callback($parentId, 'incremented', ['id' => $id, 'value' => $value, 'property' => $property]);
        $this->callback($parentId, 'incremented.'.$property, ['id' => $id, 'value' => $value]);
        $this->event($parentId, 'incremented', ['id' => $id, 'value' => $value, 'property' => $property]);
        $this->event($parentId, 'incremented.'.$property, ['id' => $id, 'value' => $value]);

        return $this;
    }
    /**
     * Decrement the specified property of the specified document.
     *
     * @param string       $parentId
     * @param mixed        $id
     * @param string|array $property
     * @param int          $value
     * @param array        $options
     *
     * @return $this
     */
    public function decrement($parentId, $id, $property, $value = 1, $options = [])
    {
        $this->callback($parentId, 'pre_decrement', ['id' => $id, 'value' => $value, 'property' => $property]);
        $this->callback($parentId, 'pre_decrement.'.$property, ['id' => $id, 'value' => $value]);

        $this->getRepository()->decrementProperty($parentId, $this->getRepoKey([$id, $property]), $value, $options);

        $this->callback($parentId, 'decremented', ['id' => $id, 'value' => $value, 'property' => $property]);
        $this->callback($parentId, 'decremented.'.$property, ['id' => $id, 'value' => $value]);
        $this->event($parentId, 'decremented', ['id' => $id, 'value' => $value, 'property' => $property]);
        $this->event($parentId, 'decremented.'.$property, ['id' => $id, 'value' => $value]);

        return $this;
    }
    /**
     * Trigger the specified document event if listener are registered.
     *
     * @param string $parentId
     * @param string $event
     * @param mixed  $data
     *
     * @return $this
     */
    protected function event($parentId, $event, $data = null)
    {
        return $this->dispatch(
            $this->buildEventName($event),
            $this->buildTypeVars([$parentId]) + (is_array($data) ? $data : [])
        );
    }
    /**
     * Execute the registered callback and return the updated subject.
     *
     * @param string $parentId
     * @param string $key
     * @param mixed  $subject
     * @param array  $options
     *
     * @return mixed
     */
    protected function callback($parentId, $key, $subject, $options = [])
    {
        return $this->getMetaDataService()->callback(
            $this->buildEventName($key),
            $subject,
            $this->buildTypeVars([$parentId]) + $options
        );
    }
    /**
     * @param string $parentId
     * @param array  $data
     * @param array  $options
     *
     * @return array
     */
    protected function prepareCreate($parentId, $data, $options = [])
    {
        $data  = $this->callback($parentId, 'create.pre_validate', $data, $options);
        $doc   = $this->validateData($data, 'create', $options);

        unset($data);

        $doc   = $this->callback($parentId, 'create.validated', $doc, $options);
        $doc   = $this->refreshModel($doc, $options);
        $doc   = $this->callback($parentId, 'pre_save', $doc, $options);
        $array = $this->convertToArray($doc, $options);
        $array = $this->callback($parentId, 'create.pre_save', $array, $options);

        return [$doc, $array];
    }
    /**
     * @param string $parentId
     * @param mixed  $doc
     * @param mixed  $array
     * @param array  $options
     *
     * @return mixed
     */
    protected function completeCreate($parentId, $doc, $array, $options = [])
    {
        $array = $this->callback($parentId, 'create.saved', $array, $options);

        $doc->id = (string) $array['_id'];

        $doc = $this->callback($parentId, 'saved', $doc, $options);
        $doc = $this->callback($parentId, 'created', $doc, $options);

        $this->event($parentId, 'created', $doc);

        return $doc;
    }
    /**
     * @param string $parentId
     * @param mixed  $id
     * @param array  $data
     * @param array  $options
     *
     * @return array
     */
    protected function prepareUpdate($parentId, $id, $data = [], $options = [])
    {
        $old = $this->get($parentId, $id, array_keys($data), $options);

        $data  = $this->callback($parentId, 'update.pre_validate', $data, $options);
        $doc   = $this->validateData($data, 'update', ['clearMissing' => false] + $options);

        unset($data);

        $doc   = $this->callback($parentId, 'update.validated', $doc, $options);
        $doc   = $this->refreshModel($doc, $options);
        $doc   = $this->callback($parentId, 'pre_save', $doc, $options);
        $array = $this->convertToArray($doc, $options);
        $array = $this->callback($parentId, 'update.pre_save', $array, $options);

        return [$doc, $array, $old];
    }
    /**
     * @param string $parentId
     * @param mixed  $id
     * @param mixed  $doc
     * @param array  $array
     * @param mixed  $old
     * @param array  $options
     *
     * @return mixed
     */
    protected function completeUpdate($parentId, $id, $doc, $array, $old, $options = [])
    {
        $this->callback($parentId, 'update.saved', $array, $options);

        unset($array);

        $doc = $this->callback($parentId, 'saved', $doc, $options);
        $doc = $this->callback($parentId, 'updated', $doc, $options);

        $this->event($parentId, 'updated', $doc);
        $this->event($parentId, 'updated_old', ['new' => $doc, 'old' => $old]);

        if ($this->observed('updated_full') || $this->observed('updated_full_old')) {
            $full = $this->get($parentId, $id, [], $options);
            $this->event($parentId, 'updated_full', $doc);
            $this->event($parentId, 'updated_full_old', ['new' => $full, 'old' => $old]);
            unset($full);
        }

        return $doc;
    }
    /**
     * @param string $parentId
     * @param mixed  $id
     * @param array  $options
     *
     * @return array
     */
    protected function prepareDelete($parentId, $id, $options = [])
    {
        $old = $this->get($parentId, $id, [], $options);

        $this->callback($parentId, 'delete.pre_save', ['id' => $id, 'old' => $old], $options);

        return [$old];
    }
    /**
     * @param string $parentId
     * @param mixed  $id
     * @param mixed  $old
     * @param array  $options
     *
     * @return mixed
     */
    protected function completeDelete($parentId, $id, $old, $options = [])
    {
        $this->callback($parentId, 'delete.saved', ['id' => $id, 'old' => $old], $options);
        $this->callback($parentId, 'deleted', ['id' => $id, 'old' => $old], $options);

        $this->event($parentId, 'deleted', $id);
        $this->event($parentId, 'deleted_old', $old);

        return $old;
    }
}
