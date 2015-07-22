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

use Exception;
use Velocity\Bundle\ApiBundle\Traits\ServiceTrait;
use Velocity\Bundle\ApiBundle\Traits\LoggerAwareTrait;
use Velocity\Bundle\ApiBundle\Traits\FormServiceAwareTrait;
use Velocity\Bundle\ApiBundle\Repository\RepositoryInterface;
use Velocity\Bundle\ApiBundle\Traits\MetaDataServiceAwareTrait;
use Velocity\Bundle\ApiBundle\Service\DocumentServiceInterface;
use Velocity\Bundle\ApiBundle\Service\MetaDataServiceAwareInterface;

/**
 * Document Service.
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
class DocumentService implements DocumentServiceInterface, MetaDataServiceAwareInterface
{
    use ServiceTrait;
    use LoggerAwareTrait;
    use FormServiceAwareTrait;
    use MetaDataServiceAwareTrait;
    /**
     * @param string $type
     *
     * @return $this
     */
    public function setType($type)
    {
        return $this->setParameter('type', $type);
    }
    /**
     * @return string
     */
    public function getType()
    {
        return $this->getParameter('type');
    }
    /**
     * @return RepositoryInterface
     */
    public function getRepository()
    {
        return $this->getService('repository');
    }
    /**
     * @param RepositoryInterface $repository
     *
     * @return $this
     */
    public function setRepository(RepositoryInterface $repository)
    {
        return $this->setService('repository', $repository);
    }
    /**
     * Build the full event name.
     *
     * @param string $event
     *
     * @return string
     */
    protected function buildEventName($event)
    {
        return sprintf('%s.%s', $this->getType(), $event);
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
        if (!$this->observed($event)) {
            return $this;
        }

        return $this->dispatch($this->buildEventName($event), $data);
    }
    /**
     * Test if specified document event has registered event listeners.
     *
     * @param string $event
     *
     * @return bool
     */
    protected function observed($event)
    {
        return $this->hasListeners($this->buildEventName($event));
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
    protected function callback($key, $subject, $options = [])
    {
        return $this->getMetaDataService()->callback(
            $this->buildEventName($key), $subject, $options
        );
    }
    /**
     * @param array $data
     * @param array $options
     *
     * @return array
     */
    protected function prepareCreate($data, $options = [])
    {
        $data  = $this->callback('create.validate.before', $data, $options);
        $doc   = $this->getFormService()->validate($this->getType(), 'create', $data, [], true, $options);
        $doc   = $this->callback('create.validate.after', $doc, $options);
        $doc   = $this->getMetaDataService()->refresh($doc, $options);
        $array = $this->getMetaDataService()->convertObjectToArray($doc, $options + ['removeNulls' => true]);
        $array = $this->callback('create.save.before', $array, $options);

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
        $array = $this->callback('create.save.after', $array, $options);

        $doc->id = (string)$array['_id'];

        $doc = $this->callback('created', $doc, $options);

        $this->event('created.refresh', $doc);
        $this->event('created', $doc);
        $this->event('created.notify', $doc);

        return $doc;
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
     * @param mixed $bulkData
     * @param array $options
     *
     * @return $this
     *
     * @throws Exception
     */
    protected function checkBulkData($bulkData, $options = [])
    {
        if (!is_array($bulkData)) {
            $this->throwException(412, "Missing bulk data");
        }

        if (!count($bulkData)) {
            $this->throwException(412, "No data to process");
        }

        unset($options);

        return $this;
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

        foreach($bulkData as $i => $data) {
            list($doc, $array) = $this->prepareCreate($data, $options);
            $docs[$i]   = $doc;
            $arrays[$i] = $array;
        }

        foreach($this->getRepository()->createBulk($arrays) as $i => $array) {
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

        foreach($bulkData as $i => $data) {
            if (isset($data['id']) && $this->has($data['id'])) {
                $toUpdate[$i] = $data;
            } else {
                $toCreate[$i] = $data;
            }
        }

        $docs = [];

        if (count($toCreate)) $docs += $this->createBulk($toCreate, $options);
        if (count($toUpdate)) $docs += $this->updateBulk($toUpdate, $options);

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

        foreach($bulkData as $i => $data) {
            if (isset($data['id']) && $this->has($data['id'])) {
                $toDelete[$i] = $data;
            } else {
                $toCreate[$i] = $data;
            }
        }

        $docs = [];

        if (count($toCreate)) $docs += $this->createBulk($toCreate, $options);
        if (count($toDelete)) $docs += $this->deleteBulk($toDelete, $options);

        return $docs;
    }
    /**
     * Count documents matching the specified criteria.
     *
     * @param mixed $criteria
     * @param array $options
     *
     * @return mixed
     */
    public function count($criteria = [], $options = [])
    {
        return $this->getRepository()->count($criteria);
    }
    /**
     * Retrieve the documents matching the specified criteria.
     *
     * @param array $criteria
     * @param array $fields
     * @param null|int $limit
     * @param int $offset
     * @param array $sorts
     * @param array $options
     *
     * @return mixed
     */
    public function find(
        $criteria = [], $fields = [], $limit = null, $offset = 0, $sorts = [], $options = []
    )
    {
        $cursor = $this->getRepository()->find($criteria, $fields, $limit, $offset, $sorts, $options);
        $data   = [];

        foreach($cursor as $k => $v) {
            $data[$k] = $this->callback('fetched', $this->convertArrayToObject($v, $options), $options);
        }

        return $data;
    }
    /**
     * Retrieve the documents matching the specified criteria and return a page with total count.
     *
     * @param array $criteria
     * @param array $fields
     * @param null|int $limit
     * @param int $offset
     * @param array $sorts
     * @param array $options
     *
     * @return mixed
     */
    public function findWithTotal(
        $criteria = [], $fields = [], $limit = null, $offset = 0, $sorts = [], $options = []
    )
    {
        return [
            $this->find($criteria, $fields, $limit, $offset, $sorts, $options),
            $this->count($criteria),
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
            $this->convertArrayToObject(
                $this->getRepository()->get($id, $fields, $options), $options
            ),
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

        foreach($this->getRepository()->find(['id' => $ids], $fields, $options) as $k => $v) {
            $docs[$k] = $this->callback('fetched', $this->convertArrayToObject($v, $options), $options);
        }

        return $docs;
    }
    /**
     * Return the specified document by the specified field.
     *
     * @param string $fieldName
     * @param mixed $fieldValue
     * @param array $fields
     * @param array $options
     *
     * @return mixed
     */
    public function getBy($fieldName, $fieldValue, $fields = [], $options = [])
    {
        return $this->callback(
            'fetched',
            $this->convertArrayToObject(
                $this->getRepository()->getBy($fieldName, $fieldValue, $fields, $options)
            ),
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
        return $this->getRepository()->getRandom($fields, $criteria, $options);
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
        $this->getRepository()->deleteFound($criteria, $options);

        return $this->event('purged');
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
        try {
            list($old) = $this->prepareDelete($id, $options);

            $this->getRepository()->delete($id, $options);

            return $this->completeDelete($id, $old, $options);
        } catch (\Exception $e) {
            if ($this->observed('delete.failed')) $this->event('delete.failed', ['id' => $id, 'exception' => $e]);
            throw $e;
        }
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

        foreach($ids as $id) {
            list($old) = $this->prepareDelete($id, $options);
            $olds[$id] = $old;
        }

        foreach($this->getRepository()->deleteBulk($ids, $options) as $id) {
            $deleteds[$id] = $this->completeDelete($id, $olds[$id], $options);
            unset($olds[$id]);
        }

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

        $this->getRepository()->update($id, ['$set' => $array], $options);

        return $this->completeUpdate($id, $doc, $array, $old, $options);
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
        $old = ($this->observed('updated.fullWithOld')
            || $this->observed('updated.fullWithOld.refresh')
            || $this->observed('updated.fullWithOld.notify'))
            ? $this->get($id) : null;

        $data  = $this->callback('update.validate.before', $data, $options);
        $doc   = $this->getFormService()->validate($this->getType(), 'update', $data, [], false, $options);
        $doc   = $this->callback('update.validate.after', $doc, $options);
        $doc   = $this->getMetaDataService()->refresh($doc, $options);
        $array = $this->getMetaDataService()->convertObjectToArray($doc, $options + ['removeNulls' => true]);
        $array = $this->callback('update.save.before', $array, $options);

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
        $this->callback('update.save.after', $array, $options);

        $doc = $this->callback('updated', $doc, $options);

        $full = ($this->observed('updated.full')
            || $this->observed('updated.full.refresh')
            || $this->observed('updated.full.notify'))
            ? $this->get($id, [], $options) : null;

        $this->event('updated.refresh', $doc);
        if (null !== $old) $this->event('updated.fullWithOld.refresh', $doc);
        if (null !== $full) $this->event('updated.full.refresh', $full);

        $this->event('updated', $doc);
        if (null !== $old) $this->event('updated.fullWithOld', $doc);
        if (null !== $full) $this->event('updated.full', $full);

        $this->event('updated.notify', $doc);
        if (null !== $old) $this->event('updated.fullWithOld.notify', $doc);
        if (null !== $full) $this->event('updated.full.notify', $full);

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
        $old = ($this->observed('deleted.withOld')
            || $this->observed('deleted.withOld.refresh')
            || $this->observed('deleted.withOld.notify'))
            ? $this->get($id) : null;

        $this->callback('delete.save.before', ['id' => $id, 'old' => $old], $options);

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
        $this->callback('delete.save.after', ['id' => $id, 'old' => $old], $options);

        $this->callback('deleted', ['id' => $id, 'old' => $old], $options);

        $this->event('deleted.refresh', ['id' => $id]);
        if (null !== $old) $this->event('deleted.withOld.refresh', $old);

        $this->event('deleted', ['id' => $id]);
        if (null !== $old) $this->event('deleted.withOld', $old);

        $this->event('deleted.notify', ['id' => $id]);
        if (null !== $old) $this->event('deleted.withOld.notify', $old);

        return ['id' => $id];
    }
    /**
     * @param array  $bulkData
     * @param array  $options
     *
     * @return $this
     */
    public function updateBulk($bulkData, $options = [])
    {
        $this->checkBulkData($bulkData, $options);

        $docs   = [];
        $arrays = [];
        $olds   = [];

        foreach($bulkData as $i => $data) {
            $id = $data['id'];
            unset($data['id']);
            list($doc, $array, $old) = $this->prepareUpdate($id, $data, $options);
            $docs[$i]   = $doc;
            $arrays[$i] = ['$set' => $array];
            $olds[$i] = $old;
        }

        foreach($this->getRepository()->updateBulk($arrays, $options) as $i => $array) {
            unset($arrays[$i]);
            $this->completeUpdate($docs[$i], $array, $olds[$i], $options);
            unset($olds[$i]);
        }

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
        $this->getRepository()->deleteFound([], $options);

        $this->event('emptied');

        return $this->createBulk($data);
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

        $docs = [];

        foreach($this->getRepository()->updateBulk($bulkData, $options) as $k => $v) {
            $docs[$k] = $this->convertArrayToObject($v, $options);
        }

        return $docs;
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
     * @param mixed $id
     * @param string|array $property
     * @param int $value
     * @param array $options
     *
     * @return $this
     */
    public function increment($id, $property, $value = 1, $options = [])
    {
        return $this->getRepository()->incrementProperty($id, $property, $value, $options);
    }
    /**
     * Decrement the specified property of the specified document.
     *
     * @param mixed $id
     * @param string|array $property
     * @param int $value
     * @param array $options
     *
     * @return $this
     */
    public function decrement($id, $property, $value = 1, $options = [])
    {
        return $this->getRepository()->decrementProperty($id, $property, $value, $options);
    }
    /**
     * Return the underlying model class.
     *
     * @param string $alias
     *
     * @return string
     */
    protected function getModelClass($alias = null)
    {
        $class = null;

        if (null !== $alias) {
            if ('.' === substr($alias, 0, 1)) {
                return $this->getModelClass() . '\\' . substr($alias, 1);
            }
            return $alias;
        }

        return sprintf('AppBundle\\Model\\%s', ucfirst($this->getType()));
    }
    /**
     * Return a new instance of the model.
     *
     * @param array $options
     *
     * @return mixed
     */
    protected function createModelInstance($options = [])
    {
        if (isset($options['model']) && !is_bool($options['model'])) {
            if (is_object($options['model'])) {
                return $options['model'];
            }
            $class = $this->getModelClass($options['model']);
        } else {
            $class = $this->getModelClass();
        }

        return new $class;
    }
    /**
     * Convert provided data (array) to a model.
     *
     * @param mixed $data
     * @param array $options
     *
     * @return mixed
     */
    protected function convertArrayToObject($data, $options = [])
    {
        return $this->getMetaDataService()->populateObject(
            $this->createModelInstance($options), $data, $options
        );
    }
}