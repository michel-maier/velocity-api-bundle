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
use Velocity\Bundle\ApiBundle\Service\FormService;
use Velocity\Bundle\ApiBundle\Service\MetaDataService;
use Velocity\Bundle\ApiBundle\Service\RepositoryService;
use Velocity\Bundle\ApiBundle\Traits\FormServiceAwareTrait;
use Velocity\Bundle\ApiBundle\Service\DocumentServiceInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Document Service.
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
class DocumentService implements DocumentServiceInterface
{
    use ServiceTrait;
    use FormServiceAwareTrait;
    /**
     * @param RepositoryService        $repository
     * @param FormService              $formService
     * @param MetaDataService          $metaDataService
     * @param EventDispatcherInterface $eventDispatcher
     * @param string                   $type
     */
    public function __construct(
        RepositoryService $repository, FormService $formService, MetaDataService $metaDataService, EventDispatcherInterface $eventDispatcher, $type = null
    )
    {
        $this->setRepository($repository);
        $this->setFormService($formService);
        $this->setMetaDataService($metaDataService);
        $this->setEventDispatcher($eventDispatcher);
        $this->setType(
            $type ?
                $type :
                lcfirst(preg_replace('/Service$/', '', basename(str_replace('\\', '/', get_class($this)))))
        );
    }
    /**
     * @param MetaDataService $service
     *
     * @return $this
     */
    public function setMetaDataService(MetaDataService $service)
    {
        return $this->setService('metaData', $service);
    }
    /**
     * @return MetaDataService
     */
    public function getMetaDataService()
    {
        return $this->getService('metaData');
    }
    /**
     * @param string $type
     *
     * @return $this
     */
    protected function setType($type)
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

        return $this->dispatch(sprintf('%s.%s', $this->getType(), $event), $data);
    }
    /**
     * @param string $event
     *
     * @return bool
     */
    protected function observed($event)
    {
        return $this->hasListeners(sprintf('%s.%s', $this->getType(), $event));
    }
    /**
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
     * @param mixed $data
     * @param array $options
     *
     * @return mixed
     */
    protected function convert($data, $options = [])
    {
        if (isset($options['model']) && false === $options['model']) {
            return $data;
        }

        $doc = $this->createModelInstance($options);

        $embeddedReferences = $this->getMetaDataService()->getEmbeddedReferencesByClass($doc);

        foreach($data as $k => $v) {
            if (isset($embeddedReferences[$k])) {
                $v = $this->mutateArrayToObject($v, $embeddedReferences[$k]['class']);
            }
            $doc->$k = $v;
        }

        return $doc;
    }
    /**
     * @param array $data
     * @param string $class
     *
     * @return Object
     */
    protected function mutateArrayToObject($data, $class)
    {
        $class = $this->getModelClass($class);

        $doc = new $class;

        foreach($data as $k => $v) {
            $doc->$k = $v;
        }

        return $doc;
    }
    /**
     * @param string $id
     * @param string $class
     * @param string $type
     *
     * @return Object
     */
    protected function convertIdToObject($id, $class, $type)
    {
        $model = $this->createModelInstance(['model' => $class]);
        $fields = array_keys(get_object_vars($model));

        return $this->{'get' . ucfirst($type) . 'Service'}()->get($id, $fields, ['model' => $model]);
    }
    /**
     * @param mixed $data
     * @param array $options
     *
     * @return mixed
     *
     * @override
     */
    protected function onCreateBeforeValidate($data, $options = [])
    {
        unset($options);

        return $data;
    }
    /**
     * @param mixed $doc
     * @param array $options
     *
     * @return mixed
     *
     * @override
     */
    protected function onCreateAfterValidate($doc, $options = [])
    {
        unset($options);

        return $doc;
    }
    /**
     * @param mixed $data
     * @param array $options
     *
     * @return array
     *
     * @override
     */
    protected function onCreateValidate($data, $options = [])
    {
        return $this->getFormService()->validate($this->getType(), 'create', $data, [], true, $options);
    }
    /**
     * @param mixed $doc
     * @param array $options
     *
     * @return mixed
     *
     * @override
     */
    protected function onCreateBeforeSave($doc, $options = [])
    {
        unset($options);

        return $doc;
    }
    /**
     * @param mixed $doc
     * @param array $options
     *
     * @return mixed
     *
     * @override
     */
    protected function onCreateSave($doc, $options = [])
    {
        unset($options);

        return $this->getRepository()->createDocument($doc);
    }
    /**
     * @param mixed $doc
     * @param array $options
     *
     * @return mixed
     *
     * @override
     */
    protected function onCreateAfterSave($doc, $options = [])
    {
        if (is_object($doc) && method_exists($doc, 'onCreateAfterSave')) {
            $doc->onCreateAfterSave($options);
        }

        return $doc;
    }
    /**
     * @param mixed $doc
     * @param array $options
     *
     * @return $this
     *
     * @override
     */
    protected function onCreateDispatch($doc, $options = [])
    {
        $this->event('created.refresh', $doc);
        $this->event('created', $doc);
        $this->event('created.notify', $doc);

        unset($options);

        return $this;
    }
    /**
     * @param string $id
     * @param array  $data
     * @param array  $options
     *
     * @return array
     *
     * @override
     */
    protected function onUpdateBeforeValidate($id, $data, $options = [])
    {
        unset($id);
        unset($options);

        return $data;
    }
    /**
     * @param string $id
     * @param array  $data
     *
     * @return array
     *
     * @override
     */
    protected function onUpdateBeforeSave($id, $data)
    {
        unset($id);

        return $data;
    }
    /**
     * @param string $id
     * @param array  $data
     *
     * @return array
     *
     * @override
     */
    protected function onUpdateAfterSave($id, $data)
    {
        unset($id);

        return $data;
    }
    /**
     * @param string $id
     *
     * @return $this
     *
     * @override
     */
    protected function onDeleteBeforeSave($id)
    {
        unset($id);

        return $this;
    }
    /**
     * @param string $id
     *
     * @return $this
     *
     * @override
     */
    protected function onDeleteAfterSave($id)
    {
        unset($id);

        return $this;
    }
    /**
     * @param array  $data
     * @param array  $cleanData
     * @param string $mode
     * @param bool   $clearMissing
     * @param array  $options
     *
     * @return array
     */
    protected function validate($data, $cleanData = [], $mode = 'create', $clearMissing = true, $options = [])
    {
        return $this->getFormService()->validate($this->getType(), $mode, $data, $cleanData, $clearMissing, $options);
    }
    /**
     * @param mixed $doc
     * @param array $options
     *
     * @return mixed
     */
    protected function onCreateFetchEmbeddedReferences($doc, $options = [])
    {
        if (!is_object($doc)) {
            return $doc;
        }

        unset($options);

        foreach ($this->getMetaDataService()->getEmbeddedReferencesByClass($doc) as $property => $embeddedReference) {
            $doc->$property = $this->convertIdToObject($doc->$property, isset($embeddedReference['class']) ? $embeddedReference['class'] : $this->getMetaDataService()->getTypeByClassAndProperty($doc, $property), $embeddedReference['type']);
        }

        return $doc;
    }
    /**
     * @param mixed $doc
     * @param array $options
     *
     * @return mixed
     */
    protected function onCreateTriggerRefreshes($doc, $options = [])
    {
        if (!is_object($doc)) {
            return $doc;
        }

        unset($options);

        foreach ($this->getMetaDataService()->getRefreshablePropertiesByClassAndOperation($doc, 'create') as $property) {
            $type = $this->getMetaDataService()->getTypeByClassAndProperty($doc, $property);
            switch($type) {
                case "DateTime<'c'>": $doc->$property = new \DateTime(); break;
                default: $this->throwException(500, sprintf("Unable to refresh model property '%s': unsupported type '%s'", $property, $type));
            }
        }

        return $doc;
    }
    /**
     * @param array $data
     * @param array $options
     *
     * @return array
     */
    public function create($data, $options = [])
    {
        $data = $this->onCreateBeforeValidate($data, $options);
        $doc  = $this->onCreateValidate($data, $options);
        $doc  = $this->onCreateAfterValidate($doc, $options);

        $doc = $this->onCreateFetchEmbeddedReferences($doc, $options);
        $doc = $this->onCreateTriggerRefreshes($doc, $options);

        $doc  = $this->onCreateBeforeSave($doc, $options);
        $doc  = $this->onCreateSave($doc, $options);
        $doc  = $this->onCreateAfterSave($doc, $options);

        $this->onCreateDispatch($doc, $options);

        return $doc;
    }
    /**
     * @param array $data
     * @param array $options
     *
     * @return array
     */
    public function createOrUpdate($data, $options = [])
    {
        if (isset($data['id']) && $this->has($data['id'])) {
            $id = $data['id'];
            unset($data['id']);
            $this->update($id, $data, $options);
            return ['id' => $id];
        }

        return $this->create($data, $options);
    }
    /**
     * @param array $bulkData
     * @param array $options
     *
     * @return array
     */
    public function createBulk($bulkData, $options = [])
    {
        if (!is_array($bulkData)) {
            $this->throwException(412, "Missing bulk data");
        }

        if (!count($bulkData)) {
            $this->throwException(412, "No data to process");
        }

        $docs = [];
        $unsaveds = [];

        foreach($bulkData as $i => $data) {
            $doc = $this->prepareCreate($data);

            $unsaveds[$i] = [];

            if (isset($doc['unsaved'])) {
                $unsaveds[$i] = $doc['unsaved'];
                unset($doc['unsaved']);
            }

            $docs[$i] = $doc;
        }

        $saveds = $this->getRepository()->createDocumentBulk($docs);

        foreach($saveds as $i => $saved) {

            $doc = $saved;

            if (isset($unsaveds[$i])) {
                $doc += $unsaveds[$i];
            }

            $saved = $this->onCreateAfterSave($doc);
            $this->event('created', $doc);
            if ($this->observed('created.full')) $this->event('created.full', $this->get($saved['id']) + (isset($options['unsaved']) ? $unsaveds[$i] : []));

            if (isset($options['unsaved'])) {
                $saveds[$i] +=  $unsaveds[$i];
            }
        }

        return $saveds;
    }
    /**
     * @param array $bulkData
     * @param array $options
     *
     * @return array
     */
    public function createOrUpdateBulk($bulkData, $options = [])
    {
        if (!is_array($bulkData)) {
            $this->throwException(412, "Missing bulk data");
        }

        if (!count($bulkData)) {
            $this->throwException(412, "No data to process");
        }

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

        if (count($toCreate)) $docs = $this->createBulk($toCreate, $options);

        if (count($toUpdate)) {
            foreach ($toUpdate as $i => $data) {
                $id = $data['id'];
                unset($data['id']);
                $this->update($id, $data, $options);
            }
        }

        return $docs;
    }
    /**
     * @param array $bulkData
     * @param array $options
     *
     * @return array
     */
    public function createOrDeleteBulk($bulkData, $options = [])
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
            if (isset($data['id']) && $this->has($data['id'])) {
                $toDelete[$i] = $data;
            } else {
                $toCreate[$i] = $data;
            }
        }

        $docs = [];

        if (count($toCreate)) $docs = $this->createBulk($toCreate, $options);

        if (count($toDelete)) {
            $this->deleteBulk(array_keys($toDelete));
        }

        return $docs;
    }
    /**
     * @param array    $criteria
     *
     * @return array
     */
    public function count($criteria = [])
    {
        if (!is_array($criteria)) $criteria = [];

        return $this->getRepository()->countDocuments($criteria);
    }
    /**
     * @param array    $criteria
     * @param array    $fields
     * @param int|null $limit
     * @param int      $offset
     * @param array    $sorts
     * @param array    $options
     *
     * @return array
     */
    public function find(
        $criteria = [], $fields = [], $limit = null, $offset = 0, $sorts = [], $options = []
    )
    {
        if (!is_array($fields))   $fields = [];
        if (!is_array($criteria)) $criteria = [];
        if (!is_array($sorts))    $sorts = [];

        $that = $this;

        return $this->getRepository()->listDocuments($criteria, $fields, $limit, $offset, $sorts,
            function ($doc) use ($options, $that) {
                $doc = $that->convert($doc, $options);
                if (isset($options['eachCallback'])) {
                    $doc = $options['eachCallback']($doc);
                }
                return $doc;
            }
        );
    }
    /**
     * @param array    $criteria
     * @param array    $fields
     * @param int|null $limit
     * @param int      $offset
     * @param array    $sorts
     * @param array    $options
     *
     * @return array
     */
    public function findWithTotal(
        $criteria = [], $fields = [], $limit = null, $offset = 0, $sorts = [], $options = []
    )
    {
        return [
            $this->find($criteria, $fields, $limit, $offset, $sorts, isset($options['eachCallback']) ? $options['eachCallback'] : null),
            $this->count($criteria),
        ];
    }
    /**
     * @param string $id
     * @param string $property
     *
     * @return mixed
     */
    public function getProperty($id, $property)
    {
        return $this->getRepository()->getDocumentProperty($id, $property);
    }
    /**
     * @param string $id
     *
     * @return bool
     */
    public function has($id)
    {
        return (bool)$this->getRepository()->hasDocument($id);
    }
    /**
     * @param string $id
     *
     * @return bool
     */
    public function hasNot($id)
    {
        return !$this->has($id);
    }
    /**
     * @param string $id
     * @param array  $fields
     * @param array  $options
     *
     * @return array
     */
    public function get($id, $fields = [], $options = [])
    {
        $doc = $this->getFields($id, $fields, $options);

        return $this->convert($doc, $options);
    }
    /**
     * @param string $id
     * @param array  $fields
     * @param array  $options
     *
     * @return array
     */
    public function getFields($id, $fields = [], $options = [])
    {
        return $this->getRepository()->getDocument($id, $fields, isset($options['criteria']) ? $options['criteria'] : []);
    }
    /**
     * @param array  $ids
     * @param array  $fields
     * @param array  $options
     *
     * @return array
     */
    public function getBulk($ids, $fields = [], $options = [])
    {
        return $this->getRepository()->listDocuments(['id' => $ids] + (isset($options['criteria']) ? $options['criteria'] : []), $fields);
    }
    /**
     * @param string $fieldName
     * @param mixed  $fieldValue
     * @param array  $fields
     * @param array  $options
     *
     * @return array
     */
    public function getBy($fieldName, $fieldValue, $fields = [], $options = [])
    {
        return $this->getRepository()->getDocumentBy($fieldName, $fieldValue, $fields, isset($options['criteria']) ? $options['criteria'] : []);
    }
    /**
     * @param array $fields
     * @param array $criteria
     * @param array $options
     *
     * @return array
     */
    public function getRandom($fields = [], $criteria = [], $options = [])
    {
        unset($options);
        srand(microtime(true));

        $idField = $this->getRepository()->getIdField() ? $this->getRepository()->getIdField() : 'id';
        $ids     = $this->find($criteria, [$idField]);
        $index   = rand(0, count($ids) - 1);
        $keys    = array_keys($ids);

        return $this->get($ids[$keys[$index]][$idField], $fields, $criteria);
    }
    /**
     * @param string $id
     *
     * @return $this
     *
     * @throws \Exception
     */
    public function checkExist($id)
    {
        if ($this->hasNot($id)) $this->throwException(404, "Unknown %s '%s'", $this->getType(), $id);

        return $this;
    }
    /**
     * @param string $id
     *
     * @return $this
     */
    public function checkNotExist($id)
    {
        if ($this->has($id)) $this->throwException(403, "%s '%s' already exist", ucfirst($this->getType()), $id);

        return $this;
    }
    /**
     * @param array $criteria
     * @param array $options
     *
     * @return $this
     */
    public function purge($criteria = [], $options = [])
    {
        unset($options);

        $this->getRepository()->deleteDocuments($criteria);

        return $this->event('purged');
    }
    /**
     * @param string $id
     * @param array  $options
     *
     * @return $this
     *
     * @throws \Exception
     */
    public function delete($id, $options = [])
    {
        try {
            unset($options);

            $deletedDoc = null;

            if ($this->observed('deleted.full')) {
                $deletedDoc = $this->get($id);
            } else {
                $this->checkExist($id);
            }

            $this->onDeleteBeforeSave($id);

            $this->getRepository()->deleteDocument($id);

            $this->onDeleteAfterSave($id);

            $this->event('deleted', ['id' => $id]);

            if (null !== $deletedDoc) {
                $this->event('deleted.full', $deletedDoc);
            }
        } catch (\Exception $e) {
            if ($this->observed('delete.failed')) {
                $this->event('delete.failed', ['id' => $id, 'exception' => $e]);
            }
            throw $e;
        }

        return $this;
    }
    /**
     * @param array $ids
     * @param array $options
     *
     * @return $this
     */
    public function deleteBulk($ids, $options = [])
    {
        unset($options);

        if (!is_array($ids)) {
            $this->throwException(412, "No list of Ids specified for deletion");
        }

        if (!count($ids)) {
            $this->throwException(412, "No ids to delete");
        }

        $deletedDocs = [];
        $properties  = [];

        foreach($ids as $id) {
            if ($this->observed('deleted.full')) {
                $deletedDocs[$id] = $this->get($id);
            } else {
                $this->checkExist($id);
            }

            $this->onDeleteBeforeSave($id);
            $properties[] = ['id' => $id];
        }

        $this->getRepository()->deleteDocuments(['$or' => $properties]);

        foreach($ids as $id) {
            $this->onDeleteAfterSave($id);

            $this->event('deleted', ['id' => $id]);

            if (isset($deletedDocs[$id])) {
                $this->event('deleted.full', $deletedDocs[$id]);
            }
        }

        return $this;
    }
    /**
     * @param string $id
     * @param array $data
     *
     * @return array
     */
    public function prepareUpdate($id, $data)
    {
        $options = [];

        if (method_exists($this, 'onUpdateConvert')) $options['convert'] = [$this, 'onUpdateConvert'];

        $data = $this->onUpdateBeforeValidate($id, $data, $options);

        $data = $this->validate($data, [], 'update', false, $options);

        $this->checkExist($id);

        $data = $this->onUpdateBeforeSave($id, $data);

        return $data;
    }
    /**
     * @param string $id
     * @param array  $doc
     * @param array  $extraData
     *
     * @return array
     */
    protected function prepareUpdateResult($id, $doc, $extraData = [])
    {
        unset($id);
        unset($doc);
        unset($extraData);

        return [];
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
        $old = null;

        if ($this->observed('updated.fullWithOld')) {
            $old = $this->get($id);
        }

        $data = $this->prepareUpdate($id, $data);

        $unsaved = null;

        if (isset($data['unsaved'])) {
            $unsaved = $data['unsaved'];
            unset($data['unsaved']);
        }

        $this->getRepository()->updateDocument($id, ['$set' => $data]);

        if (isset($unsaved)) {
            $data += $unsaved;
        }

        $data = $this->onUpdateAfterSave($id, $data);

        $this->event('updated', ['id' => $id] + $data);

        $doc = $this->get($id);

        if ($this->observed('updated.full')) $this->event('updated.full', $doc + (isset($unsaved) ? $unsaved : []));

        if (null !== $old) {
            $this->event('updated.fullWithOld', ['old' => $old] + $doc + (isset($unsaved) ? $unsaved : []));
        }

        return $this->prepareUpdateResult($id, $doc, $data);
    }
    /**
     * @param string       $id
     * @param string|array $property
     * @param int          $value
     *
     * @return $this
     */
    public function increment($id, $property, $value = 1)
    {
        if (true === is_array($property)) {
            $this->getRepository()->incrementMultipleDocumentProperties($id, $property);
        } else {
            $this->getRepository()
                ->incrementDocumentProperty($id, $property, $value);
        }

        return $this;
    }
    /**
     * @param string       $id
     * @param string|array $property
     * @param int          $value
     *
     * @return $this
     */
    public function decrement($id, $property, $value = 1)
    {
        if (!is_array($property)) return $this->increment($id, $property, -$value);

        return $this->increment(
            $id,
            array_combine(array_keys($property), array_map(function ($v) { return -$v;}, $property))
        );
    }
    /**
     * @param array $data
     * @param array $options
     *
     * @return array
     */
    public function replaceBulk($data, $options = [])
    {
        $this->getRepository()->deleteDocuments();

        $this->event('emptied');

        return $this->createBulk($data);
    }
}