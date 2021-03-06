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

use Velocity\Bundle\ApiBundle\RepositoryInterface;
use Velocity\Core\Traits\ServiceTrait;
use Symfony\Component\Form\Guess\Guess;
use Symfony\Component\Form\Guess\TypeGuess;
use Velocity\Bundle\ApiBundle\Traits\ServiceAware;

/**
 * MetaData Service.
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
class MetaDataService
{
    use ServiceTrait;
    use ServiceAware\StorageServiceAwareTrait;
    use ServiceAware\WorkflowServiceAwareTrait;
    use ServiceAware\GeneratorServiceAwareTrait;
    /**
     * @var array
     */
    protected $callbacks = [];
    /**
     * @var array
     */
    protected $models = [];
    /**
     * @var array
     */
    protected $modelIds = [];
    /**
     * @var array
     */
    protected $sdk = ['services' => []];
    /**
     * @param string                                                                                    $name
     * @param DocumentServiceInterface|SubDocumentServiceInterface|SubSubDocumentServiceInterface|mixed $service
     *
     * @return $this
     */
    public function addCrudService($name, $service)
    {
        return $this->setArrayParameterKey('crudServices', $name, $service);
    }
    /**
     * @param string $name
     *
     * @return DocumentServiceInterface|SubDocumentServiceInterface|SubSubDocumentServiceInterface|mixed
     *
     * @throws \Exception
     */
    public function getCrudService($name)
    {
        return $this->getArrayParameterKey('crudServices', $name);
    }
    /**
     * @return DocumentServiceInterface[]|SubDocumentServiceInterface[]|SubSubDocumentServiceInterface[]|array
     */
    public function getCrudServices()
    {
        return $this->getArrayParameter('crudServices');
    }
    /**
     * @param StorageService   $storageService
     * @param GeneratorService $generatorService
     * @param WorkflowService  $workflowService
     */
    public function __construct(StorageService $storageService, GeneratorService $generatorService, WorkflowService $workflowService)
    {
        $this->setStorageService($storageService);
        $this->setGeneratorService($generatorService);
        $this->setWorkflowService($workflowService);
    }
    /**
     * @param string $class
     *
     * @return $this
     *
     * @throws \Exception
     */
    public function checkModel($class)
    {
        if (!$this->isModel($class)) {
            throw $this->createUnexpectedException("Class '%s' is not registered as a model", $class);
        }

        return $this;
    }
    /**
     * @param string $class
     * @param array  $definition
     *
     * @return $this
     */
    public function addModel($class, $definition)
    {
        if (!isset($this->models[$class])) {
            $this->models[$class] = [
                'embeddedReferences'     => [],
                'embeddedReferenceLists' => [],
                'referenceLists'         => [],
                'refreshes'              => [],
                'generateds'             => [],
                'storages'               => [],
                'ids'                    => [],
                'types'                  => [],
                'fingerPrints'           => [],
                'workflows'              => [],
                'triggers'               => [],
                'cachedLists'            => [],
            ];
        }

        $this->models[$class] += $definition;

        $this->modelIds[strtolower($definition['id'])] = $class;

        return $this;
    }
    /**
     * @param $modelClass
     *
     * @return mixed|DocumentServiceInterface|SubDocumentServiceInterface|SubSubDocumentServiceInterface
     */
    public function getCrudServiceByModelClass($modelClass)
    {
        return $this->getCrudService($this->getModel($modelClass)['id']);
    }
    /**
     * @param string $type
     * @param mixed  $callback
     *
     * @return $this
     */
    public function addCallback($type, $callback)
    {
        if (!isset($this->callbacks[$type])) {
            $this->callbacks[$type] = [];
        }
        $this->callbacks[$type][] = $callback;

        return $this;
    }
    /**
     * @param string $class
     * @param string $property
     * @param array  $definition
     *
     * @return $this
     */
    public function addModelPropertyEmbeddedReference($class, $property, $definition)
    {
        $this->checkModel($class);

        $this->models[$class]['embeddedReferences'][$property] = $definition;

        return $this;
    }
    /**
     * @param string $class
     * @param string $property
     * @param array  $definition
     *
     * @return $this
     */
    public function addModelPropertyEmbeddedReferenceList($class, $property, $definition)
    {
        $this->checkModel($class);

        $this->models[$class]['embeddedReferenceLists'][$property] = $definition;

        return $this;
    }
    /**
     * @param string $class
     * @param string $property
     * @param array  $definition
     *
     * @return $this
     */
    public function addModelPropertyCachedList($class, $property, $definition)
    {
        $this->checkModel($class);

        $this->models[$class]['cachedLists'][$property] = $definition;

        $modelId = $this->models[$class]['id'];

        foreach ($definition['triggers'] as $triggerName => $trigger) {
            $sourceClass = $this->getModelClassForId($trigger['model']);
            unset($trigger['model']);
            $this->checkModel($sourceClass);
            if (!isset($this->models[$sourceClass]['triggers'])) {
                $this->models[$sourceClass]['triggers'] = [];
            }
            $this->models[$sourceClass]['triggers'][$modelId.'.'.$triggerName] = ['targetDocType' => $modelId, 'targetDocProperty' => $property] + $trigger;
        }

        return $this;
    }
    /**
     * @param string $class
     * @param string $property
     * @param array  $definition
     *
     * @return $this
     */
    public function addModelPropertyReferenceList($class, $property, $definition)
    {
        $this->checkModel($class);

        $this->models[$class]['referenceLists'][$property] = $definition;

        $values = $definition['value'];
        if (!is_array($values)) {
            $values = [];
        }

        if (!isset($this->models[$class]['types'][$property])) {
            $this->models[$class]['types'][$property] = [];
        }

        $this->models[$class]['types'][$property]['type'] = 'referenceList';
        $this->models[$class]['types'][$property]['values'] = $values;

        return $this;
    }
    /**
     * @param string $class
     * @param string $property
     * @param array  $definition
     *
     * @return $this
     */
    public function addModelPropertyRefresh($class, $property, $definition)
    {
        $this->checkModel($class);

        $operations = $definition['value'];
        if (!is_array($operations)) {
            $operations = [$operations];
        }

        foreach ($operations as $operation) {
            if (!isset($this->models[$class]['refreshes'][$operation])) {
                $this->models[$class]['refreshes'][$operation] = [];
            }

            $this->models[$class]['refreshes'][$operation][$property] = true;
        }

        return $this;
    }
    /**
     * @param string $class
     * @param string $property
     * @param array  $definition
     *
     * @return $this
     */
    public function addModelPropertyEnum($class, $property, $definition)
    {
        $this->checkModel($class);

        $values = $definition['value'];
        if (!is_array($values)) {
            $values = [];
        }

        if (!isset($this->models[$class]['types'][$property])) {
            $this->models[$class]['types'][$property] = [];
        }

        $this->models[$class]['types'][$property]['type'] = 'enum';
        $this->models[$class]['types'][$property]['values'] = $values;

        return $this;
    }
    /**
     * @param string $class
     * @param string $property
     * @param array  $definition
     *
     * @return $this
     */
    public function addModelPropertyDefaultValue($class, $property, $definition)
    {
        $this->checkModel($class);

        $value = isset($definition['value']) ? $definition['value'] : null;

        if (!isset($this->models[$class]['types'][$property])) {
            $this->models[$class]['types'][$property] = [];
        }

        $this->models[$class]['types'][$property]['default'] = $value;

        return $this;
    }
    /**
     * @param string $class
     * @param string $property
     * @param array  $definition
     *
     * @return $this
     */
    public function addModelPropertyGenerated($class, $property, $definition)
    {
        $this->checkModel($class);

        $definition['type'] = $definition['value'];
        unset($definition['value']);

        $this->models[$class]['generateds'][$property] = $definition;

        return $this;
    }
    /**
     * @param string $class
     * @param string $property
     * @param array  $definition
     *
     * @return $this
     */
    public function addModelPropertyFingerPrint($class, $property, $definition)
    {
        $this->checkModel($class);

        unset($definition['value']);

        if (!isset($definition['of'])) {
            $definition['of'] = [];
        }

        if (!is_array($definition['of'])) {
            $definition['of'] = [$definition['of']];
        }

        $this->models[$class]['fingerPrints'][$property] = $definition;

        return $this;
    }
    /**
     * @param string $class
     * @param string $property
     * @param array  $definition
     *
     * @return $this
     */
    public function addModelPropertyStorage($class, $property, $definition)
    {
        $this->checkModel($class);

        $definition['key'] = $definition['value'];
        unset($definition['value']);

        $this->models[$class]['storages'][$property] = $definition;

        return $this;
    }
    /**
     * @param string $class
     * @param string $property
     * @param array  $definition
     *
     * @return $this
     */
    public function addModelPropertyWorkflow($class, $property, $definition)
    {
        $this->checkModel($class);

        $this->models[$class]['workflows'][$property] = $definition;

        if (!isset($this->models[$class]['types'][$property])) {
            $this->models[$class]['types'][$property] = [];
        }

        $this->models[$class]['types'][$property]['type'] = 'workflow';
        $this->models[$class]['types'][$property]['values'] = isset($definition['steps']) ? $definition['steps'] : [];

        $this->getWorkflowService()->registerFromDefinition($definition['id'], $definition);

        return $this;
    }
    /**
     * @param string $class
     * @param string $property
     * @param array  $definition
     *
     * @return $this
     */
    public function addModelPropertyId($class, $property, $definition)
    {
        $this->checkModel($class);

        $definition['name'] = isset($definition['name']) ? $definition['name'] : '_id';
        $definition['property'] = $property;
        unset($definition['value']);

        $this->models[$class]['ids'] = $definition;

        return $this;
    }
    /**
     * @param string $class
     * @param string $property
     * @param array  $definition
     *
     * @return $this
     */
    public function setModelPropertyType($class, $property, $definition)
    {
        $this->checkModel($class);

        if (!isset($this->models[$class]['types'][$property])) {
            $this->models[$class]['types'][$property] = [];
        }

        $this->models[$class]['types'][$property]['type'] = $definition['name'];

        return $this;
    }
    /**
     * @param string $sourceClass
     * @param string $sourceMethod
     * @param string $route
     * @param string $service
     * @param string $method
     * @param string $type
     * @param array  $params
     * @param array  $options
     *
     * @return $this
     *
     * @throws \Exception
     */
    public function addSdkMethod($sourceClass, $sourceMethod, $route, $service, $method, $type, $params = [], $options = [])
    {
        if (!isset($this->sdk['services'][$service])) {
            $this->sdk['services'][$service] = ['methods' => []];
        }
        if (isset($this->sdk['services'][$service]['methods'][$method])) {
            throw $this->createDuplicatedException("SDK Method '%s' already registered for service '%s'", $method, $service);
        }

        $this->sdk['services'][$service]['methods'][$method] = [
            'sourceClass'  => $sourceClass,
            'sourceMethod' => $sourceMethod,
            'type'         => $type,
            'route'        => $route,
            'params'       => $params,
            'options'      => $options,
        ];

        return $this;
    }
    /**
     * @return array
     */
    public function getSdkServices()
    {
        return $this->sdk['services'];
    }
    /**
     * @param string $id
     *
     * @return string
     *
     * @throws \Exception
     */
    public function getModelClassForId($id)
    {
        if (!isset($this->modelIds[strtolower($id)])) {
            throw $this->createRequiredException("Unknown model '%s'", $id);
        }

        return $this->modelIds[strtolower($id)];
    }
    /**
     * @param string|Object $class
     *
     * @return array
     */
    public function getModelEmbeddedReferences($class)
    {
        if (is_object($class)) {
            $class = get_class($class);
        }

        $this->checkModel($class);

        return $this->models[$class]['embeddedReferences'];
    }
    /**
     * @param string|Object $class
     *
     * @return array
     */
    public function getModelReferenceLists($class)
    {
        if (is_object($class)) {
            $class = get_class($class);
        }

        $this->checkModel($class);

        return $this->models[$class]['referenceLists'];
    }
    /**
     * @param string|Object $class
     *
     * @return array
     */
    public function getModelTriggers($class, array $options = [])
    {
        if (is_object($class)) {
            $class = get_class($class);
        }

        $this->checkModel($class);

        unset($options);

        return $this->models[$class]['triggers'];
    }
    /**
     * @param string|Object $class
     * @param string        $property
     *
     * @return array
     *
     * @throws \Exception
     */
    public function getModelReferenceListByProperty($class, $property)
    {
        if (is_object($class)) {
            $class = get_class($class);
        }

        $this->checkModel($class);

        if (!isset($this->models[$class]['referenceLists'][$property])) {
            throw $this->createRequiredException("Property '%s' is a not a reference list", $property);
        }

        return $this->models[$class]['referenceLists'][$property];
    }
    /**
     * @return array
     */
    public function getModels()
    {
        return $this->models;
    }
    /**
     * @param string|object $class
     *
     * @return bool
     */
    public function isModel($class)
    {
        if (is_object($class)) {
            $class = get_class($class);
        }

        return true === isset($this->models[$class]);
    }
    /**
     * @param string|Object $class
     *
     * @return array
     */
    public function getModelTypes($class)
    {
        if (is_object($class)) {
            $class = get_class($class);
        }

        $this->checkModel($class);

        return $this->models[$class]['types'];
    }
    /**
     * @param string|Object $class
     *
     * @return array
     */
    public function getModelDefaults($class)
    {
        if (is_object($class)) {
            $class = get_class($class);
        }

        $this->checkModel($class);

        $defaults = [];

        foreach ($this->models[$class]['types'] as $property => $type) {
            if (!isset($type['default'])) {
                continue;
            }
            $defaults[$property] = $type['default'];
        }

        return $defaults;
    }
    /**
     * @param string|Object $class
     *
     * @return array
     */
    public function getModelGenerateds($class)
    {
        if (is_object($class)) {
            $class = get_class($class);
        }

        $this->checkModel($class);

        return $this->models[$class]['generateds'];
    }
    /**
     * @param string|Object $class
     *
     * @return array
     */
    public function getModelFingerPrints($class)
    {
        if (is_object($class)) {
            $class = get_class($class);
        }

        $this->checkModel($class);

        return $this->models[$class]['fingerPrints'];
    }
    /**
     * @param string|Object $class
     *
     * @return array
     */
    public function getModelStorages($class)
    {
        if (is_object($class)) {
            $class = get_class($class);
        }

        $this->checkModel($class);

        return $this->models[$class]['storages'];
    }
    /**
     * @param string|Object $class
     *
     * @return array
     */
    public function getModelWorkflows($class)
    {
        if (is_object($class)) {
            $class = get_class($class);
        }

        $this->checkModel($class);

        return $this->models[$class]['workflows'];
    }
    /**
     * @param string|Object $class
     *
     * @return array
     */
    public function getModelEmbeddedReferenceLists($class)
    {
        if (is_object($class)) {
            $class = get_class($class);
        }

        $this->checkModel($class);

        return $this->models[$class]['embeddedReferenceLists'];
    }
    /**
     * @param string|Object $class
     *
     * @return array
     */
    public function getModelCachedLists($class)
    {
        if (is_object($class)) {
            $class = get_class($class);
        }

        $this->checkModel($class);

        return $this->models[$class]['cachedLists'];
    }
    /**
     * @param string|Object $class
     *
     * @return array|null
     */
    public function getModelIdProperty($class)
    {
        if (is_object($class)) {
            $class = get_class($class);
        }

        $this->checkModel($class);

        return $this->models[$class]['ids'];
    }
    /**
     * @param string|Object $class
     * @param string        $operation
     *
     * @return array
     */
    public function getModelRefreshablePropertiesByOperation($class, $operation)
    {
        if (is_object($class)) {
            $class = get_class($class);
        }

        $this->checkModel($class);

        return isset($this->models[$class]['refreshes'][$operation])
            ? array_keys($this->models[$class]['refreshes'][$operation])
            : []
        ;
    }
    /**
     * @param string|Object $class
     * @param string        $property
     *
     * @return null|string
     */
    public function getModelPropertyType($class, $property)
    {
        if (is_object($class)) {
            $class = get_class($class);
        }

        $this->checkModel($class);

        return isset($this->models[$class]['types'][$property])
            ? $this->models[$class]['types'][$property]
            : null;
    }
    /**
     * @param string $class
     * @param string $property
     *
     * @return TypeGuess
     */
    public function getModelPropertyTypeGuess($class, $property)
    {
        $propertyType = $this->getModelPropertyType($class, $property);

        if (null === $propertyType) {
            return new TypeGuess(null, [], Guess::LOW_CONFIDENCE);
        }

        switch ($propertyType['type']) {
            case 'enum':
                return new TypeGuess('choice', ['choices' => $propertyType['values']], Guess::HIGH_CONFIDENCE);
            case 'referenceList':
                $referenceList = $this->getModelReferenceListByProperty($class, $property);
                $choices = [];
                foreach ($this->getCrudService($referenceList['type'])->find([], []) as $choice) {
                    $choice = (array) $choice;
                    $choices[$choice[$referenceList['key']]] = $choice[$referenceList['labelKey']];
                }

                return new TypeGuess('choice', ['multiple' => true, 'choices' => $choices], Guess::HIGH_CONFIDENCE);
            case 'workflow':
                return new TypeGuess('choice', ['choices' => $propertyType['steps']], Guess::HIGH_CONFIDENCE);
            default:
                return new TypeGuess(null, [], Guess::LOW_CONFIDENCE);
        }
    }
    /**
     * @param mixed $doc
     * @param array $options
     *
     * @return mixed
     */
    public function refresh($doc, $options = [])
    {
        $doc = $this->convertScalarProperties($doc, $options);
        $doc = $this->fetchEmbeddedReferences($doc, $options);
        $doc = $this->fetchEmbeddedReferenceLists($doc, $options);
        $doc = $this->triggerRefreshes($doc, $options);
        $doc = $this->buildGenerateds($doc, $options);
        $doc = $this->computeFingerPrints($doc, $options);
        $doc = $this->saveStorages($doc, $options);
        $doc = $this->loadDefaultValues($doc, $options);

        return $doc;
    }
    /**
     * @param mixed $doc
     * @param array $options
     *
     * @return mixed
     */
    public function clean($doc, $options = [])
    {
        $doc = $this->populateStorages($doc, $options);

        $this->refreshCached($doc, $options);

        return $doc;
    }
    /**
     * @param mixed $doc
     * @param array $options
     */
    protected function refreshCached($doc, array $options = [])
    {
        $triggers = $this->getModelTriggers($doc, $options);

        $options += ['operation' => null];


        foreach ($triggers as $triggerName => $trigger) {
            $targetId = $this->replaceProperty($doc, $trigger['targetId']);
            $updateData = $this->replaceProperty($doc, $trigger['targetData']);
            $requiredFields = [];
            foreach ($trigger['targetData'] as $kkk => $vvv) {
                if ('@' === $vvv{0}) {
                    $requiredFields[substr($vvv, 1)] = true;
                }
            }
            $requiredFields = array_values($requiredFields);
            $createData = $updateData;
            $updateCriteria = [];
            $createCriteria = [];
            $deleteCriteria = [];
            $skip = true;
            $list = isset($trigger['joinFieldType']) && 'list' === $trigger['joinFieldType'];
            $processNew = false;
            $processUpdated = false;
            $processDeleted = false;
            switch ($options['operation']) {
                case 'create':
                    if ($list) {
                        if (count($doc->{$trigger['joinField']})) {
                            $createCriteria['$or'] = [];
                            foreach ($doc->{$trigger['joinField']} as $kk => $vv) {
                                $createCriteria['$or'][] = ['_id' => $vv->id];
                            }
                            $processNew = true;
                            $skip = false;
                        }
                    } else {
                        $createCriteria['_id'] = $doc->{$trigger['joinField']}->id;
                        $processNew = true;
                        $skip = false;
                    }
                    break;
                case 'update':
                    $updateData = array_filter($updateData, function ($v) { return null !== $v;});
                    if ($list) {
                        $expectedIds = [];
                        $existingIds = [];
                        if (count($doc->{$trigger['joinField']})) {
                            foreach ($doc->{$trigger['joinField']} as $kk => $vv) {
                                $expectedIds[$vv->id] = true;
                            }
                        }

                        $criteria = [
                            $trigger['targetDocProperty'] . '.' . $targetId => '*notempty*',
                        ];
                        $docs = $this->getCrudService($trigger['targetDocType'])->find($criteria, ['id']);

                        if (count($docs)) {
                            foreach ($docs as $_doc) {
                                $existingIds[$_doc->id] = true;
                            }
                        }

                        $newIds = array_diff_key($expectedIds, $existingIds);
                        $deletedIds = array_diff_key($existingIds, $expectedIds);
                        $updatedIds = array_intersect_key($existingIds, $expectedIds);

                        if (count($newIds)) {
                            $createCriteria['$or'] = [];
                            foreach(array_keys($newIds) as $newId) {
                                $createCriteria['$or'][] = ['_id' => $newId];
                            }
                            $realDoc = $this->getCrudService($this->getModel($doc)['id'])->get($doc->id, $requiredFields);
                            foreach($requiredFields as $requiredField) {
                                if (isset($realDoc->$requiredField) && !isset($doc->$requiredField)) {
                                    $doc->$requiredField = $realDoc->$requiredField;
                                }
                            }
                            $skip = false;
                            $processNew = true;
                        }
                        if (count($deletedIds)) {
                            $deleteCriteria['$or'] = [];
                            foreach(array_keys($deletedIds) as $deletedId) {
                                $deleteCriteria['$or'][] = ['_id' => $deletedId];
                            }
                            $skip = false;
                            $processDeleted = true;
                        }
                        if (count($updatedIds)) {
                            $updateCriteria['$or'] = [];
                            foreach(array_keys($updatedIds) as $updatedId) {
                                $updateCriteria['$or'][] = ['_id' => $updatedId];
                            }
                            $skip = false;
                            $processUpdated = true;
                        }
                    } else {
                        $criteria = [
                            $trigger['targetDocProperty'] . '.' . $targetId => '*notempty*',
                        ];
                        $docs = $this->getCrudService($trigger['targetDocType'])->find($criteria, ['id']);

                        if (count($docs)) {
                            $updateCriteria['$or'] = [];
                            foreach ($docs as $_doc) {
                                $updateCriteria['$or'][] = ['_id' => $_doc->id];
                            }
                            $processUpdated = true;
                            $skip = false;
                        }
                    }
                    break;
                case 'delete':
                    $criteria = [
                        $trigger['targetDocProperty'].'.'.$targetId => '*notempty*',
                    ];
                    $docs = $this->getCrudService($trigger['targetDocType'])->find($criteria, ['id']);

                    if (count($docs)) {
                        $deleteCriteria['$or'] = [];
                        foreach ($docs as $_doc) {
                            $deleteCriteria['$or'][] = ['_id' => $_doc->id];
                        }
                        $skip = false;
                        $processDeleted = true;
                    }
                    break;
            }
            if (!$skip) {
                /** @var RepositoryInterface $repo */
                $repo = $this->getCrudService($trigger['targetDocType'])->getRepository();
                if ($processDeleted) {
                    $repo->unsetProperty($deleteCriteria, $trigger['targetDocProperty'] . '.' . $targetId);
                }
                if ($processNew) {
                    $repo->alter($createCriteria, ['$set' => [$trigger['targetDocProperty'].'.'.$targetId => $createData]], ['multiple' => true]);
                }
                if ($processUpdated) {
                    $updates = [];
                    foreach ($updateData as $k => $v) {
                        $updates[$trigger['targetDocProperty'].'.'.$targetId.'.'.$k] = $v;
                    }
                    if (count($updates)) {
                        $repo->alter($updateCriteria, ['$set' => $updates], ['multiple' => true]);
                    }
                }
            }
        }
    }
    /**
     * @param mixed $doc
     * @param mixed $value
     *
     * @return mixed
     */
    protected function replaceProperty($doc, $value)
    {
        if (is_array($value)) {
            foreach ($value as $k => $v) {
                $value[$k] = $this->replaceProperty($doc, $v);
            }

            return $value;
        }

        if ('@' !== $value{0}) {
            return $value;
        }

        $key = substr($value, 1);

        if (!property_exists($doc, $key)) {
            return null;
        }

        return $doc->$key;
    }
    /**
     * Process the registered callbacks.
     *
     * @param string $type
     * @param mixed  $subject
     * @param array  $options
     *
     * @return mixed
     */
    public function callback($type, $subject = null, $options = [])
    {
        if (!isset($this->callbacks[$type]) || !count($this->callbacks[$type])) {
            return $subject;
        }

        foreach ($this->callbacks[$type] as $callback) {
            $r = call_user_func_array($callback, [$subject, $options]);

            if (null !== $r) {
                $subject = $r;
            }
        }

        return $subject;
    }
    /**
     * @param string|Object $class
     *
     * @return array
     */
    public function getModel($class)
    {
        if (is_object($class)) {
            $class = get_class($class);
        }

        if ('stdClass' === $class) {
            // bypass check if stdClass (used for array to object cast)
            return [];
        }
        $this->checkModel($class);

        return $this->models[$class];
    }
    /**
     * @param mixed $doc
     * @param array $options
     *
     * @return mixed
     *
     * @throws \Exception
     */
    public function convertObjectToArray($doc, $options = [])
    {
        $options += ['removeNulls' => true];

        if (!is_object($doc)) {
            throw $this->createMalformedException('Not a valid object');
        }

        $removeNulls = true === $options['removeNulls'];

        $meta = $this->getModel($doc);
        $data = get_object_vars($doc);

        $globalObjectCast = false;
        if (get_class($doc) === 'stdClass') {
            $globalObjectCast = true;
        }
        foreach ($data as $k => $v) {
            if ($removeNulls && null === $v) {
                unset($data[$k]);
                continue;
            }
            if (isset($meta['types'][$k]['type'])) {
                switch (true) {
                    case 'DateTime' === substr($meta['types'][$k]['type'], 0, 8):
                        $data = $this->convertDataDateTimeFieldToMongoDateWithTimeZone($data, $k);
                        continue 2;
                }
            }
            if (is_object($v)) {
                $objectCast = false;
                if ('stdClass' === get_class($v)) {
                    $objectCast = true;
                }
                $v = $this->convertObjectToArray($v, $options);
                if (true === $objectCast) {
                    $v = (object) $v;
                }
            }
            $data[$k] = $v;
        }

        if (true === $globalObjectCast) {
            $data = (object) $data;
        }

        return $data;
    }
    /**
     * @param mixed $doc
     * @param array $data
     * @param array $options
     *
     * @return mixed
     */
    public function populateObject($doc, $data = [], $options = [])
    {
        if (isset($data['_id']) && !isset($data['id'])) {
            $data['id'] = (string) $data['_id'];
            unset($data['_id']);
        } else {
            if (isset($data['id'])) {
                $data['id'] = (string) $data['id'];
            }
        }

        $doc = $this->mutateArrayToObject($data, $doc, $options);

        return $doc;
    }
    /**
     * @param mixed  $doc
     * @param mixed  $data
     * @param string $propertyName
     * @param array  $options
     *
     * @return mixed
     *
     * @throws \Exception
     */
    public function populateObjectProperty($doc, $data, $propertyName, $options = [])
    {
        if (!property_exists($doc, $propertyName)) {
            throw $this->createRequiredException("Property '%s' does not exist on %s", $propertyName, get_class($doc));
        }

        $doc->$propertyName = $data;

        $this->populateStorages($doc, $options);

        return $doc->$propertyName;
    }
    /**
     * @param mixed $doc
     * @param array $options
     *
     * @return mixed
     */
    protected function fetchEmbeddedReferences($doc, $options = [])
    {
        if (!is_object($doc)) {
            return $doc;
        }

        foreach ($this->getModelEmbeddedReferences($doc) as $property => $embeddedReference) {
            if (!$this->isPopulableModelProperty($doc, $property, $options)) {
                continue;
            }
            $type = $this->getModelPropertyType($doc, $property);
            $doc->$property = $this->convertIdToObject($doc->$property, isset($embeddedReference['class']) ? $embeddedReference['class'] : ($type ? $type['type'] : null), $embeddedReference['type']);
        }

        return $doc;
    }
    /**
     * @param mixed  $doc
     * @param string $property
     * @param array  $options
     *
     * @return bool
     */
    protected function isPopulableModelProperty($doc, $property, array $options = [])
    {
        return property_exists($doc, $property) && (!isset($options['populateNulls']) || (false === $options['populateNulls'] && null !== $doc->$property));
    }
    /**
     * @param mixed $doc
     * @param array $options
     *
     * @return mixed
     *
     * @throws \Exception
     */
    protected function fetchEmbeddedReferenceLists($doc, $options = [])
    {
        if (!is_object($doc)) {
            return $doc;
        }

        foreach ($this->getModelEmbeddedReferenceLists($doc) as $property => $embeddedReferenceList) {
            if (!$this->isPopulableModelProperty($doc, $property, $options)) {
                continue;
            }
            $type = $this->getModelPropertyType($doc, $property);
            if (!is_array($doc->$property)) {
                $doc->$property = [];
            }
            $subDocs = [];
            foreach ($doc->$property as $kk => $subDocKey) {
                $tt = isset($embeddedReferenceList['class']) ? $embeddedReferenceList['class'] : ($type ? $type['type'] : null);
                if (null !== $tt) {
                    $tt = preg_replace('/^array<([^>]+)>$/', '\\1', $tt);
                }
                $subDoc = $this->convertIdToObject($subDocKey, $tt, $embeddedReferenceList['type']);
                unset($doc->$property[$kk]);
                if (!isset($subDoc->id)) {
                    throw $this->createRequiredException('Property id is empty for embedded reference', 500);
                }
                $subDocs[$subDoc->id] = $subDoc;
            }
            $doc->$property = (object) $subDocs;
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

        return $this->getCrudService($type)->get($id, $fields, ['model' => $model]);
    }
    /**
     * @param mixed $doc
     * @param array $options
     *
     * @return mixed
     */
    protected function buildGenerateds($doc, $options = [])
    {
        if (!is_object($doc)) {
            return $doc;
        }

        $generateds = $this->getModelGenerateds($doc);

        foreach ($generateds as $k => $v) {
            $generate = false;
            if (isset($v['trigger'])) {
                if (isset($doc->{$v['trigger']})) {
                    $generate = true;
                }
            } else {
                if ($this->isPopulableModelProperty($doc, $k, $options)) {
                    $generate = true;
                }
            }
            if (true === $generate) {
                $doc->$k = $this->generateValue($v, $doc);
            }
        }

        return $doc;
    }
    /**
     * @param mixed $doc
     * @param array $options
     *
     * @return mixed
     */
    protected function loadDefaultValues($doc, $options = [])
    {
        if (!is_object($doc)) {
            return $doc;
        }

        if (!isset($options['operation']) || 'create' !== $options['operation']) {
            return $doc;
        }

        $defaults = $this->getModelDefaults($doc);

        foreach ($defaults as $k => $v) {
            if (!$this->isPopulableModelProperty($doc, $k, $options)) {
                continue;
            }
            $doc->$k = $v;
        }

        return $doc;
    }
    /**
     * @param mixed $doc
     * @param array $options
     *
     * @return mixed
     */
    protected function computeFingerPrints($doc, $options = [])
    {
        if (!is_object($doc)) {
            return $doc;
        }

        $fingerPrints = $this->getModelFingerPrints($doc);

        foreach ($fingerPrints as $k => $v) {
            $values = [];

            $found = false;

            foreach ($v['of'] as $p) {
                if (!isset($doc->$p)) {
                    $values[$p] = null;
                    continue;
                } else {
                    $values[$p] = $doc->$p;
                    $found = true;
                }
            }

            unset($v['of']);

            if (true === $found) {
                $doc->$k = $this->generateValue(['type' => 'fingerprint'], count($values) > 1 ? $values : array_shift($values));
            }
        }

        unset($options);

        return $doc;
    }
    /**
     * @param mixed $doc
     * @param array $options
     *
     * @return mixed
     */
    protected function saveStorages($doc, $options = [])
    {
        if (!is_object($doc)) {
            return $doc;
        }

        $vars = ((array) $doc);

        foreach ($options as $k => $v) {
            if (!isset($vars[$k])) {
                $vars[$k] = $v;
            }
        }

        $storages = $this->getModelStorages($doc);

        foreach ($storages as $k => $definition) {
            if (!$this->isPopulableModelProperty($doc, $k, $options)) {
                continue;
            }
            $doc->$k = $this->saveStorageValue($doc->$k, $definition, $vars);
        }

        return $doc;
    }
    /**
     * @param mixed $doc
     * @param array $options
     *
     * @return mixed
     */
    protected function populateStorages($doc, $options = [])
    {
        if (!is_object($doc)) {
            return $doc;
        }

        unset($options);

        $storages = $this->getModelStorages($doc);

        foreach ($storages as $k => $definition) {
            if (isset($doc->$k)) {
                $doc->$k = $this->readStorageValue($doc->$k);
            }
            unset($definition);
        }

        return $doc;
    }
    /**
     * @param mixed $doc
     * @param array $options
     *
     * @return mixed
     *
     * @throws \Exception
     */
    protected function triggerRefreshes($doc, $options = [])
    {
        if (!is_object($doc)) {
            return $doc;
        }

        if (!isset($options['operation'])) {
            return $doc;
        }

        foreach ($this->getModelRefreshablePropertiesByOperation($doc, $options['operation']) as $property) {
            $type = $this->getModelPropertyType($doc, $property);
            switch ($type['type']) {
                case "DateTime<'c'>":
                    $doc->$property = new \DateTime();
                    break;
                default:
                    throw $this->createUnexpectedException("Unable to refresh model property '%s': unsupported type '%s'", $property, $type);
            }
        }

        return $doc;
    }
    /**
     * @param mixed $doc
     * @param array $options
     *
     * @return mixed
     */
    protected function convertScalarProperties($doc, $options = [])
    {
        if (!is_object($doc)) {
            return $doc;
        }

        $types = $this->getModelTypes($doc);

        foreach ($types as $property => $type) {
            if (!$this->isPopulableModelProperty($doc, $property, ['populateNulls' => false] + $options)) {
                continue;
            }
            switch ($type['type']) {
                case "DateTime<'c'>":
                    if ('' === $doc->$property) {
                        $doc->$property = null;
                    }
                    break;
            }
        }

        return $doc;
    }
    /**
     * @param array $definition
     * @param mixed $data
     *
     * @return string
     *
     * @throws \Exception
     */
    protected function generateValue($definition, $data)
    {
        return $this->getGeneratorService()->generate($definition['type'], is_object($data) ? (array) $data : $data);
    }
    /**
     * @param mixed $value
     * @param array $definition
     * @param mixed $vars
     *
     * @return string
     *
     * @throws \Exception
     */
    protected function saveStorageValue($value, $definition, $vars)
    {
        $key = $definition['key'];

        if (0 < preg_match_all('/\{([^\}]+)\}/', $key, $matches)) {
            foreach ($matches[1] as $i => $match) {
                $key = str_replace($matches[0][$i], isset($vars[$match]) ? $vars[$match] : null, $key);
            }
        }

        $this->getStorageService()->save($key, $value);

        return $key;
    }
    /**
     * @param mixed $key
     *
     * @return mixed
     *
     * @throws \Exception
     */
    protected function readStorageValue($key)
    {
        return $this->getStorageService()->read($key);
    }
    /**
     * @param array $options
     *
     * @return object
     */
    protected function createModelInstance(array $options)
    {
        $class = $options['model'];

        return new $class();
    }
    /**
     * @param array $data
     * @param mixed $doc
     * @param array $options
     *
     * @return Object
     */
    protected function mutateArrayToObject($data, $doc, array $options = [])
    {
        $embeddedReferences = $this->getModelEmbeddedReferences($doc);
        $embeddedReferenceLists = $this->getModelEmbeddedReferenceLists($doc);
        $cachedLists = $this->getModelCachedLists($doc);
        $types = $this->getModelTypes($doc);

        foreach ($data as $k => $v) {
            if (!property_exists($doc, $k)) {
                continue;
            }
            if (isset($embeddedReferences[$k])) {
                $v = $this->mutateArrayToObject($v, $this->createModelInstance(['model' => $embeddedReferences[$k]['class']]), $options);
            }
            if (isset($embeddedReferenceLists[$k])) {
                $tt = isset($embeddedReferenceLists[$k]['class']) ? $embeddedReferenceLists[$k]['class'] : (isset($types[$k]) ? $types[$k]['type'] : null);
                if (null !== $tt) {
                    $tt = preg_replace('/^array<([^>]+)>$/', '\\1', $tt);
                }
                if (!is_array($v)) {
                    $v = [];
                }
                $subDocs = [];
                foreach ($v as $kk => $vv) {
                    $subDocs[$kk] = $this->mutateArrayToObject($vv, $this->createModelInstance(['model' => $tt]), $options);
                }
                $v = $subDocs;
            }
            if (isset($cachedLists[$k])) {
                $tt = isset($cachedLists[$k]['class']) ? $cachedLists[$k]['class'] : (isset($types[$k]) ? $types[$k]['type'] : null);
                if (null !== $tt) {
                    $tt = preg_replace('/^array<([^>]+)>$/', '\\1', $tt);
                }
                if (!is_array($v)) {
                    $v = [];
                }
                $subDocs = [];
                foreach ($v as $kk => $vv) {
                    $subDocs[$kk] = $this->mutateArrayToObject($vv, $this->createModelInstance(['model' => $tt]), $options);
                }
                $v = $subDocs;
            }
            if (isset($types[$k])) {
                switch (true) {
                    case 'DateTime' === substr($types[$k]['type'], 0, 8):
                        $data = $this->revertDocumentMongoDateWithTimeZoneFieldToDateTime($data, $k);
                        $v = $data[$k];
                }
                $doc->$k = $v;
            } else {
                $doc->$k = $v;
            }
        }

        $doc = $this->populateStorages($doc);

        return $doc;
    }
    /**
     * @param array  $data
     * @param string $fieldName
     *
     * @return array
     *
     * @throws \Exception
     */
    protected function convertDataDateTimeFieldToMongoDateWithTimeZone($data, $fieldName)
    {
        if (!isset($data[$fieldName])) {
            throw $this->createRequiredException("Missing date time field '%s'", $fieldName);
        }

        if (null !== $data[$fieldName] && !$data[$fieldName] instanceof \DateTime) {
            throw $this->createRequiredException("Field '%s' must be a valid DateTime", $fieldName);
        }

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
     *
     * @throws \Exception
     */
    protected function revertDocumentMongoDateWithTimeZoneFieldToDateTime($doc, $fieldName)
    {
        if (!isset($doc[$fieldName])) {
            throw $this->createRequiredException("Missing mongo date field '%s'", $fieldName);
        }

        if (!isset($doc[sprintf('%s_tz', $fieldName)])) {
            $doc[sprintf('%s_tz', $fieldName)] = date_default_timezone_get();
        }

        if (!$doc[$fieldName] instanceof \MongoDate) {
            throw $this->createMalformedException("Field '%s' must be a valid MongoDate", $fieldName);
        }

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
}
