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

        $this->getRepository()->setProperty($pParentId, $this->getRepoKey([$parentId, $doc['id']]), $doc);

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
            list($docs[$i], $arrays[$i]) = $this->prepareCreate($pParentId, $parentId, $data, $options);
        }

        foreach ($this->getRepository()->setProperties($parentId, $arrays, $options) as $i => $array) {
            unset($arrays[$i]);
            $this->completeCreate($pParentId, $parentId, $docs[$i], $array, $options);
        }

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
            if (isset($data['id']) && $this->has($pParentId, $parentId, $data['id'])) {
                $toUpdate[$i] = $data;
            } else {
                $toCreate[$i] = $data;
            }
        }

        $docs = [];

        if (count($toCreate)) {
            $docs += $this->createBulk($pParentId, $parentId, $toCreate, $options);
        }
        if (count($toUpdate)) {
            $docs += $this->updateBulk($pParentId, $parentId, $toUpdate, $options);
        }

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
        }

        $docs = [];

        if (count($toCreate)) {
            $docs += $this->createBulk($pParentId, $parentId, $toCreate, $options);
        }
        if (count($toDelete)) {
            $docs += $this->deleteBulk($pParentId, $parentId, $toDelete, $options);
        }

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

        $items = $this->getRepository()->getProperty($pParentId, $this->getRepoKey([$parentId]), $options);

        if (!is_array($items) || !count($items)) {
            return 0;
        }

        $this->filterItems($items, $criteria);

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

        $items = $this->getRepository()->getProperty($pParentId, $this->getRepoKey([$parentId]));

        if (!is_array($items) || !count($items)) {
            return [];
        }

        $this->sortItems($items, $sorts, $options);
        $this->filterItems($items, $criteria, $fields, $options);
        $this->paginateItems($items, $limit, $offset, $options);

        foreach ($items as $k => $v) {
            $items[$k] = $this->callback($pParentId, $parentId, 'fetched', $this->convertArrayToObject($v, $options), $options);
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
        return count($this->find($pParentId, $parentId, [$fieldName => $fieldValue], 1, 0, $options));
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
        unset($fields);

        return $this->callback(
            $pParentId,
            $parentId,
            'fetched',
            $this->convertArrayToObject(
                $this->getRepository()->getProperty($pParentId, $this->getRepoKey([$parentId, $id]), $options),
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
            throw $this->createException(500, "Purging sub documents with criteria not supported.");
        }

        $this->getRepository()->setProperty($pParentId, $this->getRepoKey([$parentId]), (object) []);

        unset($criteria);
        unset($options);

        return $this->event($pParentId, $parentId, 'purged');
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
        try {
            list($old) = $this->prepareDelete($pParentId, $parentId, $id, $options);

            $this->getRepository()->unsetProperty($pParentId, $this->getRepoKey([$parentId, $id]), $options);

            return $this->completeDelete($pParentId, $parentId, $id, $old, $options);
        } catch (\Exception $e) {
            if ($this->observed('delete.failed')) {
                $this->event($pParentId, $parentId, 'delete.failed', ['id' => $id, 'exception' => $e]);
            }
            throw $e;
        }
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
            list($old)  = $this->prepareDelete($pParentId, $parentId, $id, $options);
            $olds[$id]  = $old;
            $deleteds[$id] = $this->getRepoKey([$parentId, $id]);
        }

        if (count($deleteds)) {
            $this->getRepository()->unsetProperty($pParentId, array_values($deleteds), $options);
        }

        foreach (array_keys($deleteds) as $id) {
            $deleteds[$id] = $this->completeDelete($pParentId, $parentId, $id, $olds[$id], $options);
            unset($olds[$id]);
        }

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

        $this->getRepository()->update($pParentId, ['$set' => $array], $options);

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
        $docs = $this->find($pParentId, $parentId, [$fieldName => $fieldValue], ['id'], $options);

        if (!count($docs)) {
            throw $this->createException(
                404,
                "Unknown %s with %s '%s' for %s '%' in %s '%s'",
                $this->getSubSubType(),
                $fieldName,
                $fieldValue,
                $this->getSubType(),
                $parentId,
                $this->getType(),
                $pParentId
            );
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
            $changes += $this->mutateArrayToRepoChanges($arrays[$i], [$parentId, $id]);
        }

        $this->getRepository()->setProperties($pParentId, $changes, $options);

        foreach ($arrays as $i => $array) {
            $this->completeUpdate($pParentId, $parentId, $i, $docs[$i], $array, $olds[$i], $options);
            unset($arrays[$array]);
        }

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
        $this->getRepository()->setProperty($pParentId, $this->getRepoKey([$parentId]), (object) [], $options);

        $this->event($pParentId, $parentId, 'emptied');

        return $this->createBulk($pParentId, $parentId, $data);
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
        if ($this->hasNot($pParentId, $parentId, $id, $options)) {
            throw $this->createException(
                404,
                "Unknown %s '%s' in %s '%s' for %s '%s'",
                $this->getSubSubType(),
                $id,
                $this->getSubType(),
                $parentId,
                $this->getType(),
                $pParentId
            );
        }

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
        if ($this->has($pParentId, $parentId, $id, $options)) {
            throw $this->createException(
                404,
                "%s '%s' already exist in %s '%s' for %s '%s'",
                ucfirst($this->getSubSubType()),
                $id,
                $this->getSubType(),
                $parentId,
                $this->getType(),
                $pParentId
            );
        }

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
        $repoKey = sprintf('%s.%s', $this->getRepoKey([$parentId, $id]), $property);

        $this->getRepository()->incrementProperty($pParentId, $repoKey, $value, $options);

        return $this->event($pParentId, 'updated', ['id' => $id] + [$id => $this->getRepository()->getProperty($pParentId, $repoKey)]);
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
        $repoKey = sprintf('%s.%s', $this->getRepoKey([$parentId, $id]), $property);

        $this->getRepository()->decrementProperty($pParentId, $repoKey, $value, $options);

        return $this->event($pParentId, 'updated', ['id' => $id] + [$id => $this->getRepository()->getProperty($pParentId, $repoKey)]);
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
        if (!$this->observed($event)) {
            return $this;
        }

        return $this->dispatch(
            $this->buildEventName($event), $this->buildTypeVars([$pParentId, $parentId]) + (is_array($data) ? $data : [])
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
        unset($pParentId);
        unset($parentId);

        return $this->getMetaDataService()->callback(
            $this->buildEventName($key),
            $subject,
            $options
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
        $data  = $this->callback($pParentId, $parentId, 'create.validate.before', $data, $options);
        $doc   = $this->getFormService()->validate($this->getFullType(), 'create', $data, [], true, $options);
        $doc   = $this->callback($pParentId, $parentId, 'create.validate.after', $doc, $options);
        $doc   = $this->getMetaDataService()->refresh($doc, $options);
        $doc   = $this->callback($pParentId, $parentId, 'save.before', $doc, $options);
        $array = $this->getMetaDataService()->convertObjectToArray($doc, $options + ['removeNulls' => true]);
        $array = $this->callback($pParentId, $parentId, 'create.save.before', $array, $options);

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
        $this->callback($pParentId, $parentId, 'create.save.after', $array, $options);

        $doc = $this->callback($pParentId, $parentId, 'save.after', $doc, $options);
        $doc = $this->callback($pParentId, $parentId, 'created', $doc, $options);

        $this->event($pParentId, $parentId, 'created.refresh', $doc);
        $this->event($pParentId, $parentId, 'created', $doc);
        $this->event($pParentId, $parentId, 'created.notify', $doc);

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
        $old = ($this->observed('updated.fullWithOld')
            || $this->observed('updated.fullWithOld.refresh')
            || $this->observed('updated.fullWithOld.notify'))
            ? $this->get($pParentId, $parentId, $id) : null;

        $data  = $this->callback($pParentId, $parentId, 'update.validate.before', $data, $options);
        $doc   = $this->getFormService()->validate($this->getFullType(), 'update', $data, [], false, $options);
        $doc   = $this->callback($pParentId, $parentId, 'update.validate.after', $doc, $options);
        $doc   = $this->getMetaDataService()->refresh($doc, $options);
        $array = $this->getMetaDataService()->convertObjectToArray($doc, $options + ['removeNulls' => true]);
        $array = $this->callback($pParentId, $parentId, 'update.save.before', $array, $options);

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
        $this->callback($pParentId, $parentId, 'update.save.after', $array, $options);

        $doc = $this->callback($pParentId, $parentId, 'updated', $doc, $options);

        $full = ($this->observed('updated.full')
            || $this->observed('updated.full.refresh')
            || $this->observed('updated.full.notify'))
            ? $this->get($pParentId, $parentId, $id, [], $options) : null;

        $this->event($pParentId, $parentId, 'updated.refresh', $doc);
        if (null !== $old) {
            $this->event($pParentId, $parentId, 'updated.fullWithOld.refresh', $doc);
        }
        if (null !== $full) {
            $this->event($pParentId, $parentId, 'updated.full.refresh', $full);
        }

        $this->event($pParentId, $parentId, 'updated', $doc);
        if (null !== $old) {
            $this->event($pParentId, $parentId, 'updated.fullWithOld', $doc);
        }
        if (null !== $full) {
            $this->event($pParentId, $parentId, 'updated.full', $full);
        }

        $this->event($pParentId, $parentId, 'updated.notify', $doc);
        if (null !== $old) {
            $this->event($pParentId, $parentId, 'updated.fullWithOld.notify', $doc);
        }
        if (null !== $full) {
            $this->event($pParentId, $parentId, 'updated.full.notify', $full);
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
        $old = ($this->observed('deleted.withOld')
            || $this->observed('deleted.withOld.refresh')
            || $this->observed('deleted.withOld.notify'))
            ? $this->get($pParentId, $parentId, $id) : null;

        $this->callback($pParentId, $parentId, 'delete.save.before', ['id' => $id, 'old' => $old], $options);

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
        $this->callback($pParentId, $parentId, 'delete.save.after', ['id' => $id, 'old' => $old], $options);

        $this->callback($pParentId, $parentId, 'deleted', ['id' => $id, 'old' => $old], $options);

        $this->event($pParentId, $parentId, 'deleted.refresh', ['id' => $id]);
        if (null !== $old) {
            $this->event($pParentId, $parentId, 'deleted.withOld.refresh', $old);
        }

        $this->event($pParentId, $parentId, 'deleted', ['id' => $id]);
        if (null !== $old) {
            $this->event($pParentId, $parentId, 'deleted.withOld', $old);
        }

        $this->event($pParentId, $parentId, 'deleted.notify', ['id' => $id]);
        if (null !== $old) {
            $this->event($pParentId, $parentId, 'deleted.withOld.notify', $old);
        }

        return ['id' => $id];
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
        $repoKey = $this->getRepoKey([$id]);

        $this->getRepository()->setProperty($parentId, $repoKey, array_merge($this->getRepository()->getProperty($parentId, $repoKey), $data));

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
        $this->getRepository()->setProperty($parentId, $this->getRepoKey([$id]), $value);

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
                    return $that->getRepoKey([$a]);
                },
                array_keys($values)
            ),
            array_values($values)
        );

        $this->getRepository()->setProperties($parentId, $values);

        return $this;
    }
}
