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
 * Document Service.
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
class DocumentService implements DocumentServiceInterface
{
    use ModelServiceTrait;
    /**
     * @return int
     */
    public function getExpectedTypeCount()
    {
        return 1;
    }
    /**
     * Create a new document.
     *
     * @param mixed $data
     * @param array $options
     *
     * @return mixed
     */
    public function create($data, $options = [])
    {
        list($doc, $array) = $this->prepareCreate($data, $options);

        unset($data);

        $this->getRepository()->create($array, $options);

        return $this->completeCreate($doc, $array, $options);
    }
    /**
     * Create document if not exist or update it.
     *
     * @param mixed $data
     * @param array $options
     *
     * @return mixed
     */
    public function createOrUpdate($data, $options = [])
    {
        if (isset($data['id']) && $this->has($data['id'])) {
            $id = $data['id'];
            unset($data['id']);

            return $this->update($id, $data, $options);
        }

        return $this->create($data, $options);
    }
    /**
     * Create a list of documents.
     *
     * @param mixed $bulkData
     * @param array $options
     *
     * @return mixed
     */
    public function createBulk($bulkData, $options = [])
    {
        $this->checkBulkData($bulkData, $options);

        $docs   = [];
        $arrays = [];

        foreach ($bulkData as $i => $data) {
            list($docs[$i], $arrays[$i]) = $this->prepareCreate($data, $options);
            unset($bulkData[$i]);
        }

        foreach ($this->getRepository()->createBulk($arrays, $options) as $i => $array) {
            unset($arrays[$i]);
            $this->completeCreate($docs[$i], $array, $options);
        }

        return $docs;
    }
    /**
     * Create documents if not exist or update them.
     *
     * @param mixed $bulkData
     * @param array $options
     *
     * @return mixed
     */
    public function createOrUpdateBulk($bulkData, $options = [])
    {
        $this->checkBulkData($bulkData, $options);

        $toCreate = [];
        $toUpdate = [];

        foreach ($bulkData as $i => $data) {
            unset($bulkData[$i]);
            if (isset($data['id']) && $this->has($data['id'])) {
                $toUpdate[$i] = $data;
            } else {
                $toCreate[$i] = $data;
            }
        }

        unset($bulkData);

        $docs = [];

        if (count($toCreate)) {
            $docs += $this->createBulk($toCreate, $options);
        }

        unset($toCreate);

        if (count($toUpdate)) {
            $docs += $this->updateBulk($toUpdate, $options);
        }

        unset($toUpdate);

        return $docs;
    }
    /**
     * Create documents if not exist or delete them.
     *
     * @param mixed $bulkData
     * @param array $options
     *
     * @return mixed
     */
    public function createOrDeleteBulk($bulkData, $options = [])
    {
        $this->checkBulkData($bulkData, $options);

        $toCreate = [];
        $toDelete = [];

        foreach ($bulkData as $i => $data) {
            if (isset($data['id']) && $this->has($data['id'])) {
                $toDelete[$i] = $data;
            } else {
                $toCreate[$i] = $data;
            }
            unset($bulkData[$i]);
        }

        unset($bulkData);

        $docs = [];

        if (count($toCreate)) {
            $docs += $this->createBulk($toCreate, $options);
        }

        unset($toCreate);

        if (count($toDelete)) {
            $docs += $this->deleteBulk($toDelete, $options);
        }

        unset($toDelete);

        return $docs;
    }
    /**
     * Count documents matching the specified criteria.
     *
     * @param mixed $criteria
     * @param array $options
     *
     * @return int
     */
    public function count($criteria = [], $options = [])
    {
        return $this->getRepository()->count($criteria, $options);
    }
    /**
     * Retrieve the documents matching the specified criteria.
     *
     * @param array    $criteria
     * @param array    $fields
     * @param null|int $limit
     * @param int      $offset
     * @param array    $sorts
     * @param array    $options
     *
     * @return mixed
     */
    public function find($criteria = [], $fields = [], $limit = null, $offset = 0, $sorts = [], $options = [])
    {
        $cursor = $this->getRepository()->find($criteria, $fields, $limit, $offset, $sorts, $options);
        $data   = [];

        unset($criteria, $fields, $limit, $offset, $sorts);

        foreach ($cursor as $k => $v) {
            $data[$k] = $this->callback('fetched', $this->convertToModel($v, $options), $options);
        }

        unset($cursor);

        return $data;
    }
    /**
     * Retrieve the documents matching the specified criteria and return a page with total count.
     *
     * @param array    $criteria
     * @param array    $fields
     * @param null|int $limit
     * @param int      $offset
     * @param array    $sorts
     * @param array    $options
     *
     * @return mixed
     */
    public function findWithTotal($criteria = [], $fields = [], $limit = null, $offset = 0, $sorts = [], $options = [])
    {
        return [
            $this->find($criteria, $fields, $limit, $offset, $sorts, $options),
            $this->count($criteria, $options),
        ];
    }
    /**
     * Return the property of the specified document.
     *
     * @param mixed  $id
     * @param string $property
     * @param array  $options
     *
     * @return mixed
     */
    public function getProperty($id, $property, $options = [])
    {
        return $this->getRepository()->getProperty($id, $property, $options);
    }
    /**
     * Test if specified document exist.
     *
     * @param mixed $id
     * @param array $options
     *
     * @return bool
     */
    public function has($id, $options = [])
    {
        return $this->getRepository()->has($id, $options);
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
        return $this->getRepository()->hasBy($fieldName, $fieldValue, $options);
    }
    /**
     * Test if specified document does not exist.
     *
     * @param mixed $id
     * @param array $options
     *
     * @return bool
     */
    public function hasNot($id, $options = [])
    {
        return $this->getRepository()->hasNot($id, $options);
    }
    /**
     * Return the specified document.
     *
     * @param mixed $id
     * @param array $fields
     * @param array $options
     *
     * @return mixed
     */
    public function get($id, $fields = [], $options = [])
    {
        return $this->callback(
            'fetched',
            $this->convertToModel($this->getRepository()->get($id, $fields, $options), $options),
            $options
        );
    }
    /**
     * Return the list of the specified documents.
     *
     * @param array $ids
     * @param array $fields
     * @param array $options
     *
     * @return mixed
     */
    public function getBulk($ids, $fields = [], $options = [])
    {
        $docs = [];

        foreach ($this->getRepository()->find(['_id' => $ids], $fields, $options) as $k => $v) {
            $docs[$k] = $this->callback('fetched', $this->convertToModel($v, $options), $options);
        }

        return $docs;
    }
    /**
     * Return the specified document by the specified field.
     *
     * @param string $fieldName
     * @param mixed  $fieldValue
     * @param array  $fields
     * @param array  $options
     *
     * @return mixed
     */
    public function getBy($fieldName, $fieldValue, $fields = [], $options = [])
    {
        return $this->callback(
            'fetched',
            $this->convertToModel($this->getRepository()->getBy($fieldName, $fieldValue, $fields, $options)),
            $options
        );
    }
    /**
     * Return a random document matching the specified criteria.
     *
     * @param array $fields
     * @param array $criteria
     * @param array $options
     *
     * @return mixed
     */
    public function getRandom($fields = [], $criteria = [], $options = [])
    {
        return $this->callback(
            'fetched',
            $this->convertToModel($this->getRepository()->getRandom($fields, $criteria, $options)),
            $options
        );
    }
    /**
     * Purge all the documents matching the specified criteria.
     *
     * @param array $criteria
     * @param array $options
     *
     * @return mixed
     */
    public function purge($criteria = [], $options = [])
    {
        $this->callback('pre_purge', $criteria, $options);

        $this->getRepository()->deleteFound($criteria, $options);

        $this->callback('purged', $criteria, $options);
        $this->event('purged');

        return $this;
    }
    /**
     * Delete the specified document.
     *
     * @param mixed $id
     * @param array $options
     *
     * @return mixed
     *
     * @throws Exception
     */
    public function delete($id, $options = [])
    {
        list($old) = $this->prepareDelete($id, $options);

        $this->getRepository()->delete($id, $options);

        return $this->completeDelete($id, $old, $options);
    }
    /**
     * Delete the specified documents.
     *
     * @param array $ids
     * @param array $options
     *
     * @return mixed
     */
    public function deleteBulk($ids, $options = [])
    {
        $this->checkBulkData($ids, $options);

        $olds     = [];
        $deleteds = [];

        foreach ($ids as $id) {
            list($olds[$id]) = $this->prepareDelete($id, $options);
            unset($ids[$id]);
        }


        foreach ($this->getRepository()->deleteBulk($ids, $options) as $id) {
            $deleteds[$id] = $this->completeDelete($id, $olds[$id], $options);
            unset($olds[$id]);
        }

        unset($ids);
        unset($olds);

        return $deleteds;
    }
    /**
     * @param string $id
     * @param array  $data
     * @param array  $options
     *
     * @return $this
     */
    public function update($id, $data, $options = [])
    {
        list($doc, $array, $old) = $this->prepareUpdate($id, $data, $options);

        unset($data);

        $this->getRepository()->update($id, $array, $options);

        return $this->completeUpdate($id, $doc, $array, $old, $options);
    }
    /**
     * @param mixed $fieldName
     * @param mixed $fieldValue
     * @param array $data
     * @param array $options
     *
     * @return $this
     */
    public function updateBy($fieldName, $fieldValue, $data, $options = [])
    {
        return $this->update($this->getBy($fieldName, $fieldValue, ['id'], $options)->id, $data, $options);
    }
    /**
     * @param array $bulkData
     * @param array $options
     *
     * @return $this
     */
    public function updateBulk($bulkData, $options = [])
    {
        $this->checkBulkData($bulkData, $options);

        $docs   = [];
        $arrays = [];
        $olds   = [];

        foreach ($bulkData as $i => $data) {
            $id = $data['id'];
            unset($data['id']);
            list($docs[$i], $arrays[$i], $olds[$i]) = $this->prepareUpdate($id, $data, $options);
            unset($bulkData[$i]);
        }

        unset($bulkData);

        foreach ($this->getRepository()->updateBulk($arrays, $options) as $i => $array) {
            unset($arrays[$i]);
            $this->completeUpdate($docs[$i], $array, $olds[$i], $options);
            unset($olds[$i]);
        }

        unset($olds);
        unset($arrays);

        return $docs;
    }
    /**
     * Replace all the specified documents.
     *
     * @param array $data
     * @param array $options
     *
     * @return mixed
     */
    public function replaceAll($data, $options = [])
    {
        $this->callback('pre_empty', []);

        $this->getRepository()->deleteFound([], $options);

        $this->callback('emptied', []);
        $this->event('emptied');

        return $this->createBulk($data, $options);
    }
    /**
     * Replace all the specified documents.
     *
     * @param array $bulkData
     * @param array $options
     *
     * @return mixed
     */
    public function replaceBulk($bulkData, $options = [])
    {
        $this->checkBulkData($bulkData, $options);

        $ids = [];

        foreach ($bulkData as $k => $v) {
            if (!isset($v['id'])) {
                throw $this->createException(412, 'Missing id for item #%s', $k);
            }
            $ids[] = $v['id'];
        }

        $this->deleteBulk($ids, $options);

        unset($ids);

        return $this->createBulk($bulkData, $options);
    }
    /**
     * Check if specified document exist.
     *
     * @param mixed $id
     * @param array $options
     *
     * @return $this
     *
     * @throws Exception
     */
    public function checkExist($id, $options = [])
    {
        $this->getRepository()->checkExist($id, $options);

        return $this;
    }
    /**
     * Check is specified document does not exist.
     *
     * @param mixed $id
     * @param array $options
     *
     * @return $this
     *
     * @throws Exception
     */
    public function checkNotExist($id, $options = [])
    {
        $this->getRepository()->checkNotExist($id, $options);

        return $this;
    }
    /**
     * Increment the specified property of the specified document.
     *
     * @param mixed        $id
     * @param string|array $property
     * @param int          $value
     * @param array        $options
     *
     * @return $this
     */
    public function increment($id, $property, $value = 1, $options = [])
    {
        $this->callback('pre_increment', ['id' => $id, 'value' => $value, 'property' => $property]);
        $this->callback('pre_increment.'.$property, ['id' => $id, 'value' => $value]);

        $this->getRepository()->incrementProperty($id, $property, $value, $options);

        $this->callback('incremented', ['id' => $id, 'value' => $value, 'property' => $property]);
        $this->callback('incremented.'.$property, ['id' => $id, 'value' => $value]);
        $this->event('incremented', ['id' => $id, 'value' => $value, 'property' => $property]);
        $this->event('incremented.'.$property, ['id' => $id, 'value' => $value]);

        return $this;
    }
    /**
     * Decrement the specified property of the specified document.
     *
     * @param mixed        $id
     * @param string|array $property
     * @param int          $value
     * @param array        $options
     *
     * @return $this
     */
    public function decrement($id, $property, $value = 1, $options = [])
    {
        $this->callback('pre_decrement', ['id' => $id, 'value' => $value, 'property' => $property]);
        $this->callback('pre_decrement.'.$property, ['id' => $id, 'value' => $value]);

        $this->getRepository()->decrementProperty($id, $property, $value, $options);

        $this->callback('decremented', ['id' => $id, 'value' => $value, 'property' => $property]);
        $this->callback('decremented.'.$property, ['id' => $id, 'value' => $value]);
        $this->event('decremented', ['id' => $id, 'value' => $value, 'property' => $property]);
        $this->event('decremented.'.$property, ['id' => $id, 'value' => $value]);

        return $this;
    }
    /**
     * Trigger the specified document event if listener are registered.
     *
     * @param string $event
     * @param mixed  $data
     *
     * @return $this
     */
    protected function event($event, $data = null)
    {
        return $this->dispatch($this->buildEventName($event), is_array($data) ? $data : null);
    }
    /**
     * Execute the registered callback and return the updated subject.
     *
     * @param string $key
     * @param mixed  $subject
     * @param array  $options
     *
     * @return mixed
     */
    protected function callback($key, $subject = null, $options = [])
    {
        return $this->getMetaDataService()->callback($this->buildEventName($key), $subject, $options);
    }
    /**
     * @param array $data
     * @param array $options
     *
     * @return array
     */
    protected function prepareCreate($data, $options = [])
    {
        $data  = $this->callback('create.pre_validate', $data, $options);
        $doc   = $this->validateData($data, 'create', $options);

        unset($data);

        $doc   = $this->callback('create.validated', $doc, $options);
        $doc   = $this->refreshModel($doc, $options);
        $doc   = $this->callback('pre_save', $doc, $options);
        $array = $this->convertToArray($doc, $options);
        $array = $this->callback('create.pre_save', $array, $options);

        return [$doc, $array];
    }
    /**
     * @param $doc
     * @param $array
     * @param array $options
     *
     * @return mixed
     */
    protected function completeCreate($doc, $array, $options = [])
    {
        $array = $this->callback('create.saved', $array, $options);

        $doc->id = (string) $array['_id'];

        $doc = $this->callback('saved', $doc, $options);
        $doc = $this->callback('created', $doc, $options);

        $this->event('created', $doc);

        return $doc;
    }
    /**
     * @param mixed $id
     * @param array $data
     * @param array $options
     *
     * @return array
     */
    protected function prepareUpdate($id, $data = [], $options = [])
    {
        $old = $this->get($id, array_keys($data), $options);

        $data  = $this->callback('update.pre_validate', $data, $options);
        $doc   = $this->validateData('update', $data, ['clearMissing' => false] + $options);

        unset($data);

        $doc   = $this->callback('update.validated', $doc, $options);
        $doc   = $this->refreshModel($doc, $options);
        $doc   = $this->callback('pre_save', $doc, $options);
        $array = $this->convertToArray($doc, $options);
        $array = $this->callback('update.pre_save', $array, $options);

        return [$doc, $array, $old];
    }
    /**
     * @param mixed $id
     * @param mixed $doc
     * @param array $array
     * @param mixed $old
     * @param array $options
     *
     * @return mixed
     */
    protected function completeUpdate($id, $doc, $array, $old, $options = [])
    {
        $this->callback('update.saved', $array, $options);

        unset($array);

        $doc = $this->callback('saved', $doc, $options);
        $doc = $this->callback('updated', $doc, $options);

        $this->event('updated', $doc);
        $this->event('updated_old', ['new' => $doc, 'old' => $old]);

        if ($this->observed('updated_full') || $this->observed('updated_full_old')) {
            $full = $this->get($id, [], $options);
            $this->event('updated_full', $doc);
            $this->event('updated_full_old', ['new' => $full, 'old' => $old]);
            unset($full);
        }

        return $doc;
    }
    /**
     * @param mixed $id
     * @param array $options
     *
     * @return array
     */
    protected function prepareDelete($id, $options = [])
    {
        $old = $this->get($id, [], $options);

        $this->callback('delete.pre_save', ['id' => $id, 'old' => $old], $options);

        return [$old];
    }
    /**
     * @param mixed $id
     * @param mixed $old
     * @param array $options
     *
     * @return mixed
     */
    protected function completeDelete($id, $old, $options = [])
    {
        $this->callback('delete.saved', ['id' => $id, 'old' => $old], $options);
        $this->callback('deleted', ['id' => $id, 'old' => $old], $options);

        $this->event('deleted', $id);
        $this->event('deleted_old', $old);

        return $old;
    }
}
