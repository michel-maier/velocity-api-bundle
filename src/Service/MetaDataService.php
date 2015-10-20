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

use Doctrine\Common\Collections\ArrayCollection;
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
     */
    public function __construct(StorageService $storageService, GeneratorService $generatorService)
    {
        $this->setStorageService($storageService);
        $this->setGeneratorService($generatorService);
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
            ];
        }

        $this->models[$class] += $definition;

        return $this;
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

        $this->models[$class]['types'][$property] = [
            'type' => $definition['name'],
        ];

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
        $doc   = $this->convertScalarProperties($doc, $options);
        $doc   = $this->fetchEmbeddedReferences($doc, $options);
        $doc   = $this->fetchEmbeddedReferenceLists($doc, $options);
        $doc   = $this->triggerRefreshes($doc, $options);
        $doc   = $this->buildGenerateds($doc, $options);
        $doc   = $this->saveStorages($doc, $options);

        return $doc;
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
                $v = $this->convertObjectToArray($v, $options);
            }
            $data[$k] = $v;
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
        $embeddedReferences = $this->getModelEmbeddedReferences($doc);
        $embeddedReferenceLists = $this->getModelEmbeddedReferenceLists($doc);
        $types = $this->getModelTypes($doc);

        if (isset($data['_id']) && !isset($data['id'])) {
            $data['id'] = (string) $data['_id'];
            unset($data['_id']);
        } else {
            if (isset($data['id'])) {
                $data['id'] = (string) $data['id'];
            }
        }

        foreach ($data as $k => $v) {
            if (!property_exists($doc, $k)) {
                continue;
            }
            if (isset($embeddedReferences[$k])) {
                $v = $this->mutateArrayToObject($v, $embeddedReferences[$k]['class']);
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
                    $subDocs[$kk] = $this->mutateArrayToObject($vv, $tt);
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

        unset($options);

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

        unset($options);

        foreach ($this->getModelEmbeddedReferences($doc) as $property => $embeddedReference) {
            $type = $this->getModelPropertyType($doc, $property);
            $doc->$property = $this->convertIdToObject($doc->$property, isset($embeddedReference['class']) ? $embeddedReference['class'] : ($type ? $type['type'] : null), $embeddedReference['type']);
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
    protected function fetchEmbeddedReferenceLists($doc, $options = [])
    {
        if (!is_object($doc)) {
            return $doc;
        }

        unset($options);

        foreach ($this->getModelEmbeddedReferenceLists($doc) as $property => $embeddedReferenceList) {
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
            $doc->$property = $subDocs;
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

        unset($options);

        $generateds = $this->getModelGenerateds($doc);

        foreach ($generateds as $k => $v) {
            $doc->$k = $this->generateValue($v, $doc);
        }

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

        unset($options);

        $storages = $this->getModelStorages($doc);

        foreach ($storages as $k => $definition) {
            $doc->$k = $this->saveStorageValue($doc->$k, $definition, $doc);
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

        unset($options);

        foreach ($this->getModelRefreshablePropertiesByOperation($doc, 'create') as $property) {
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

        unset($options);

        $types = $this->getModelTypes($doc);

        foreach ($types as $property => $type) {
            if (property_exists($doc, $property) && null === $doc->$property) {
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
     * @param mixed $entireDoc
     *
     * @return string
     *
     * @throws \Exception
     */
    protected function generateValue($definition, $entireDoc)
    {
        return $this->getGeneratorService()->generate($definition['type'], (array) $entireDoc);
    }
    /**
     * @param mixed $value
     * @param array $definition
     * @param mixed $entireDoc
     *
     * @return string
     *
     * @throws \Exception
     */
    protected function saveStorageValue($value, $definition, $entireDoc)
    {
        $key = $definition['key'];
        $data = (array) $entireDoc;

        if (0 < preg_match_all('/\{([^\}]+)\}/', $key, $matches)) {
            foreach ($matches[1] as $i => $match) {
                $key = str_replace($matches[0][$i], isset($data[$match]) ? $data[$match] : null, $key);
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
     * @param string $class
     *
     * @return Object
     */
    protected function mutateArrayToObject($data, $class)
    {
        if (is_object($class)) {
            $class = get_class($class);
        }

        $doc = $this->createModelInstance(['model' => $class]);

        foreach ($data as $k => $v) {
            $doc->$k = $v;
        }

        return $doc;
    }


    /**
     * @move
     */
    protected function convert2($data)
    {
        foreach ($data as $k => $v) {
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
     * @move
     */
    protected function revert($doc)
    {
        foreach (array_keys($doc) as $k) {
            if (!isset($doc[$k])) {
                continue; // if key was removed by previous iteration
            }            if ('Date' === substr($k, -4)) {
                $doc = $this->revertDocumentMongoDateWithTimeZoneFieldToDateTime($doc, $k);
            } elseif (is_array($doc[$k])) {
                $doc[$k] = $this->revert($doc[$k]);
            }
        }

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
