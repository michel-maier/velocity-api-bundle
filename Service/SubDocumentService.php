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

        $this->getRepository()->setProperty($parentId, $this->getRepoKey([$doc->id]), $doc);

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
            list($doc, $array) = $this->prepareCreate($parentId, $data, $options);
            $docs[$i]   = $doc;
            $arrays[$i] = $array;
        }

        foreach ($this->getRepository()->setProperties($parentId, $arrays, $options) as $i => $array) {
            unset($arrays[$i]);
            $this->completeCreate($parentId, $docs[$i], $array, $options);
        }

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
            if (isset($data['id']) && $this->has($parentId, $data['id'])) {
                $toUpdate[$i] = $data;
            } else {
                $toCreate[$i] = $data;
            }
        }

        $docs = [];

        if (count($toCreate)) {
            $docs += $this->createBulk($parentId, $toCreate, $options);
        }
        if (count($toUpdate)) {
            $docs += $this->updateBulk($parentId, $toUpdate, $options);
        }

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
        }

        $docs = [];

        if (count($toCreate)) {
            $docs += $this->createBulk($parentId, $toCreate, $options);
        }
        if (count($toDelete)) {
            $docs += $this->deleteBulk($parentId, $toDelete, $options);
        }

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

        $items = $this->getRepository()->getProperty($parentId, $this->getRepoKey(), $options);

        if (!is_array($items) || !count($items)) {
            return 0;
        }

        $this->filterItems($items, $criteria);

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

        $items = $this->getRepository()->getProperty($parentId, $this->getRepoKey());

        if (!is_array($items) || !count($items)) {
            return [];
        }

        $this->sortItems($items, $sorts, $options);
        $this->filterItems($items, $criteria, $fields, $options);
        $this->paginateItems($items, $limit, $offset, $options);

        foreach ($items as $k => $v) {
            $items[$k] = $this->callback('fetched', $this->convertArrayToObject($v, $options), $options);
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
        return count($this->find($parentId, [$fieldName => $fieldValue], 1, 0, $options));
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
        unset($fields);

        return $this->callback(
            'fetched',
            $this->convertArrayToObject(
                $this->getRepository()->getProperty($parentId, $this->getRepoKey([$id]), $options),
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
            throw $this->createException(500, "Purging sub documents with criteria not supported.");
        }

        $this->getRepository()->setProperty($parentId, $this->getRepoKey(), (object) []);

        unset($criteria);
        unset($options);

        return $this->event($parentId, 'purged');
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
        try {
            list($old) = $this->prepareDelete($parentId, $id, $options);

            $this->getRepository()->unsetProperty($parentId, $this->getRepoKey([$id]), $options);

            return $this->completeDelete($id, $old, $options);
        } catch (\Exception $e) {
            if ($this->observed('delete.failed')) {
                $this->event($parentId, 'delete.failed', ['id' => $id, 'exception' => $e]);
            }
            throw $e;
        }
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
            list($old)  = $this->prepareDelete($parentId, $id, $options);
            $olds[$id]  = $old;
            $deleteds[$id] = $this->getRepoKey([$id]);
        }

        if (count($deleteds)) {
            $this->getRepository()->unsetProperty($parentId, array_values($deleteds), $options);
        }

        foreach (array_keys($deleteds) as $id) {
            $deleteds[$id] = $this->completeDelete($parentId, $id, $olds[$id], $options);
            unset($olds[$id]);
        }

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

        $this->getRepository()->update($id, ['$set' => $array], $options);

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
        $docs = $this->find($parentId, [$fieldName => $fieldValue], ['id'], $options);

        if (!count($docs)) {
            throw $this->createException(
                404,
                "Unknown %s with %s '%s' in %s '%s'",
                $this->getSubType(),
                $fieldName,
                $fieldValue,
                $this->getType(),
                $parentId
            );
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
            $changes += $this->mutateArrayToRepoChanges($arrays[$i], [$id]);
        }

        $this->getRepository()->setProperties($parentId, $changes, $options);

        foreach ($arrays as $i => $array) {
            $this->completeUpdate($parentId, $i, $docs[$i], $array, $olds[$i], $options);
            unset($arrays[$array]);
        }

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
        $this->getRepository()->setProperty($parentId, $this->getRepoKey(), (object) [], $options);

        $this->event($parentId, 'emptied');

        return $this->createBulk($parentId, $data);
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
        if ($this->hasNot($parentId, $id, $options)) {
            throw $this->createException(
                404,
                "Unknown %s '%s' for %s '%s'",
                $this->getSubType(),
                $id,
                $this->getType(),
                $parentId
            );
        }

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
        if ($this->has($parentId, $id, $options)) {
            throw $this->createException(
                404,
                "%s '%s' already exist for %s '%s'",
                ucfirst($this->getSubType()),
                $id,
                $this->getType(),
                $parentId
            );
        }

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
        $repoKey = sprintf('%s.%s', $this->getRepoKey([$id]), $property);

        $this->getRepository()->incrementProperty($parentId, $repoKey, $value, $options);

        return $this->event($parentId, 'updated', ['id' => $id] + [$id => $this->getRepository()->getProperty($parentId, $repoKey)]);
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
        $repoKey = sprintf('%s.%s', $this->getRepoKey([$id]), $property);

        $this->getRepository()->decrementProperty($parentId, $repoKey, $value, $options);

        return $this->event($parentId, 'updated', ['id' => $id] + [$id => $this->getRepository()->getProperty($parentId, $repoKey)]);
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
        if (!$this->observed($event)) {
            return $this;
        }

        return $this->dispatch(
            $this->buildEventName($event), $this->buildTypeVars([$parentId]) + (is_array($data) ? $data : [])
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
        unset($parentId);

        return $this->getMetaDataService()->callback(
            $this->buildEventName($key),
            $subject,
            $options
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
        $data  = $this->callback($parentId, 'create.validate.before', $data, $options);
        $doc   = $this->getFormService()->validate($this->getFullType(), 'create', $data, [], true, $options);
        $doc   = $this->callback($parentId, 'create.validate.after', $doc, $options);
        $doc   = $this->getMetaDataService()->refresh($doc, $options);
        $doc   = $this->callback($parentId, 'save.before', $doc, $options);
        $array = $this->getMetaDataService()->convertObjectToArray($doc, $options + ['removeNulls' => true]);
        $array = $this->callback($parentId, 'create.save.before', $array, $options);

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
        $array = $this->callback($parentId, 'create.save.after', $array, $options);

        $doc->id = (string) $array['_id'];

        $doc = $this->callback($parentId, 'save.after', $doc, $options);
        $doc = $this->callback($parentId, 'created', $doc, $options);

        $this->event($parentId, 'created.refresh', $doc);
        $this->event($parentId, 'created', $doc);
        $this->event($parentId, 'created.notify', $doc);

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
        $old = ($this->observed('updated.fullWithOld')
            || $this->observed('updated.fullWithOld.refresh')
            || $this->observed('updated.fullWithOld.notify'))
            ? $this->get($parentId, $id) : null;

        $data  = $this->callback($parentId, 'update.validate.before', $data, $options);
        $doc   = $this->getFormService()->validate($this->getFullType(), 'update', $data, [], false, $options);
        $doc   = $this->callback($parentId, 'update.validate.after', $doc, $options);
        $doc   = $this->getMetaDataService()->refresh($doc, $options);
        $array = $this->getMetaDataService()->convertObjectToArray($doc, $options + ['removeNulls' => true]);
        $array = $this->callback($parentId, 'update.save.before', $array, $options);

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
        $this->callback($parentId, 'update.save.after', $array, $options);

        $doc = $this->callback($parentId, 'updated', $doc, $options);

        $full = ($this->observed('updated.full')
            || $this->observed('updated.full.refresh')
            || $this->observed('updated.full.notify'))
            ? $this->get($parentId, $id, [], $options) : null;

        $this->event($parentId, 'updated.refresh', $doc);
        if (null !== $old) {
            $this->event($parentId, 'updated.fullWithOld.refresh', $doc);
        }
        if (null !== $full) {
            $this->event($parentId, 'updated.full.refresh', $full);
        }

        $this->event($parentId, 'updated', $doc);
        if (null !== $old) {
            $this->event($parentId, 'updated.fullWithOld', $doc);
        }
        if (null !== $full) {
            $this->event($parentId, 'updated.full', $full);
        }

        $this->event($parentId, 'updated.notify', $doc);
        if (null !== $old) {
            $this->event($parentId, 'updated.fullWithOld.notify', $doc);
        }
        if (null !== $full) {
            $this->event($parentId, 'updated.full.notify', $full);
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
        $old = ($this->observed('deleted.withOld')
            || $this->observed('deleted.withOld.refresh')
            || $this->observed('deleted.withOld.notify'))
            ? $this->get($parentId, $id) : null;

        $this->callback($parentId, 'delete.save.before', ['id' => $id, 'old' => $old], $options);

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
        $this->callback($parentId, 'delete.save.after', ['id' => $id, 'old' => $old], $options);

        $this->callback($parentId, 'deleted', ['id' => $id, 'old' => $old], $options);

        $this->event($parentId, 'deleted.refresh', ['id' => $id]);
        if (null !== $old) {
            $this->event($parentId, 'deleted.withOld.refresh', $old);
        }

        $this->event($parentId, 'deleted', ['id' => $id]);
        if (null !== $old) {
            $this->event($parentId, 'deleted.withOld', $old);
        }

        $this->event($parentId, 'deleted.notify', ['id' => $id]);
        if (null !== $old) {
            $this->event($parentId, 'deleted.withOld.notify', $old);
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

        $this->getRepository()->setProperty(
            $parentId,
            $repoKey,
            array_merge($this->getRepository()->getProperty($parentId, $repoKey), $data)
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
