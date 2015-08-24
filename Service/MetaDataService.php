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

use Velocity\Bundle\ApiBundle\Traits\ServiceTrait;

/**
 * MetaData Service.
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
class MetaDataService
{
    use ServiceTrait;
    /**
     * @var array
     */
    protected $classes = [];
    /**
     * @var array
     */
    protected $callbacks = [];
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
    public function addClassPropertyEmbeddedReference($class, $property, $definition)
    {
        if (!isset($this->classes[$class])) {
            $this->classes[$class] = [];
        }
        if (!isset($this->classes[$class]['embeddedReferences'])) {
            $this->classes[$class]['embeddedReferences'] = [];
        }

        $this->classes[$class]['embeddedReferences'][$property] = $definition;

        return $this;
    }
    /**
     * @param string $class
     * @param string $property
     * @param array  $definition
     *
     * @return $this
     */
    public function addClassPropertyEmbeddedReferenceList($class, $property, $definition)
    {
        if (!isset($this->classes[$class])) {
            $this->classes[$class] = [];
        }
        if (!isset($this->classes[$class]['embeddedReferenceLists'])) {
            $this->classes[$class]['embeddedReferenceLists'] = [];
        }

        $this->classes[$class]['embeddedReferenceLists'][$property] = $definition;

        return $this;
    }
    /**
     * @param string $class
     * @param string $property
     * @param array  $definition
     *
     * @return $this
     */
    public function addClassPropertyRefresh($class, $property, $definition)
    {
        if (!isset($this->classes[$class])) {
            $this->classes[$class] = [];
        }
        if (!isset($this->classes[$class]['refreshes'])) {
            $this->classes[$class]['refreshes'] = [];
        }

        $operations = $definition['value'];
        if (!is_array($operations)) $operations = [$operations];

        foreach($operations as $operation) {
            if (!isset($this->classes[$class]['refreshes'][$operation])) {
                $this->classes[$class]['refreshes'][$operation] = [];
            }

            $this->classes[$class]['refreshes'][$operation][$property] = true;
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
    public function addClassPropertyGenerated($class, $property, $definition)
    {
        if (!isset($this->classes[$class])) {
            $this->classes[$class] = [];
        }
        if (!isset($this->classes[$class]['generateds'])) {
            $this->classes[$class]['generateds'] = [];
        }

        $definition['type'] = $definition['value'];
        unset($definition['value']);

        $this->classes[$class]['generateds'][$property] = $definition;

        return $this;
    }
    /**
     * @param string $class
     * @param string $property
     * @param array  $definition
     *
     * @return $this
     */
    public function addClassPropertyId($class, $property, $definition)
    {
        if (!isset($this->classes[$class])) {
            $this->classes[$class] = [];
        }
        if (!isset($this->classes[$class]['ids'])) {
            $this->classes[$class]['ids'] = [];
        }

        $definition['name'] = isset($definition['name']) ? $definition['name'] : '_id';
        $definition['property'] = $property;
        unset($definition['value']);

        $this->classes[$class]['ids'] = $definition;

        return $this;
    }
    /**
     * @param string $class
     * @param string $property
     * @param array  $definition
     *
     * @return $this
     */
    public function setClassPropertyType($class, $property, $definition)
    {
        if (!isset($this->classes[$class])) {
            $this->classes[$class] = [];
        }
        if (!isset($this->classes[$class]['types'])) {
            $this->classes[$class]['types'] = [];
        }
        $this->classes[$class]['types'][$property] = $definition['name'];

        return $this;
    }
    /**
     * @param string|Object $class
     *
     * @return array
     */
    public function getEmbeddedReferencesByClass($class)
    {
        if (is_object($class)) {
            $class = get_class($class);
        }

        return isset($this->classes[$class]['embeddedReferences'])
            ? $this->classes[$class]['embeddedReferences']
            : []
        ;
    }
    /**
     * @param string|Object $class
     *
     * @return array
     */
    public function getTypesByClass($class)
    {
        if (is_object($class)) {
            $class = get_class($class);
        }

        return isset($this->classes[$class]['types'])
            ? $this->classes[$class]['types']
            : []
        ;
    }
    /**
     * @param string|Object $class
     *
     * @return array
     */
    public function getGeneratedsByClass($class)
    {
        if (is_object($class)) {
            $class = get_class($class);
        }

        return isset($this->classes[$class]['generateds'])
            ? $this->classes[$class]['generateds']
            : []
        ;
    }
    /**
     * @param string|Object $class
     *
     * @return array
     */
    public function getEmbeddedReferenceListsByClass($class)
    {
        if (is_object($class)) {
            $class = get_class($class);
        }

        return isset($this->classes[$class]['embeddedReferenceLists'])
            ? $this->classes[$class]['embeddedReferenceLists']
            : []
        ;
    }
    /**
     * @param string|Object $class
     *
     * @return array|null
     */
    public function getIdPropertyByClass($class)
    {
        if (is_object($class)) {
            $class = get_class($class);
        }

        return isset($this->classes[$class]['ids'])
            ? $this->classes[$class]['ids']
            : null
        ;
    }
    /**
     * @param string|Object $class
     * @param string        $operation
     *
     * @return array
     */
    public function getRefreshablePropertiesByClassAndOperation($class, $operation)
    {
        if (is_object($class)) {
            $class = get_class($class);
        }

        return isset($this->classes[$class]['refreshes'][$operation])
            ? array_keys($this->classes[$class]['refreshes'][$operation])
            : []
        ;
    }
    /**
     * @param string|Object $class
     * @param string $property
     *
     * @return null|string
     */
    public function getTypeByClassAndProperty($class, $property)
    {
        if (is_object($class)) {
            $class = get_class($class);
        }

        return isset($this->classes[$class]['types'][$property])
            ? $this->classes[$class]['types'][$property]
            : null;
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

        foreach ($this->getEmbeddedReferencesByClass($doc) as $property => $embeddedReference) {
            $doc->$property = $this->convertIdToObject($doc->$property, isset($embeddedReference['class']) ? $embeddedReference['class'] : $this->getTypeByClassAndProperty($doc, $property), $embeddedReference['type']);
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

        $generateds = $this->getGeneratedsByClass($doc);

        foreach($generateds as $k => $v) {
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
    protected function triggerRefreshes($doc, $options = [])
    {
        if (!is_object($doc)) {
            return $doc;
        }

        unset($options);

        foreach ($this->getRefreshablePropertiesByClassAndOperation($doc, 'create') as $property) {
            $type = $this->getTypeByClassAndProperty($doc, $property);
            switch($type) {
                case "DateTime<'c'>": $doc->$property = new \DateTime(); break;
                default: throw $this->createException(500, sprintf("Unable to refresh model property '%s': unsupported type '%s'", $property, $type));
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

        $types = isset($this->classes[get_class($doc)]['types']) ? $this->classes[get_class($doc)]['types'] : [];

        foreach ($types as $property => $type) {
            if (property_exists($doc, $property) && null === $doc->$property) {
                continue;
            }
            switch($type) {
                case "DateTime<'c'>":
                    if ('' === $doc->$property) $doc->$property = null;
                    break;
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
    public function refresh($doc, $options = [])
    {
        $doc   = $this->convertScalarProperties($doc, $options);
        $doc   = $this->fetchEmbeddedReferences($doc, $options);
        $doc   = $this->triggerRefreshes($doc, $options);
        $doc   = $this->buildGenerateds($doc, $options);

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

        foreach($this->callbacks[$type] as $callback) {
            $r = call_user_func_array($callback, [$subject, $options]);

            if (null !== $r) {
                $subject = $r;
            }
        }

        return $subject;
    }
    /**
     * @param $doc
     * @param array $options
     * @return mixed
     */
    public function convertObjectToArray($doc, $options = [])
    {
        if (!is_object($doc)) {
            throw $this->createException(412, "Not a valid object");
        }

        $removeNulls = isset($options['removeNulls']) && true === $options['removeNulls'];

        $meta = [];

        if (isset($this->classes[get_class($doc)])) {
            $meta = $this->classes[get_class($doc)];
        }

        $data = get_object_vars($doc);

        foreach($data as $k => $v) {
            if ($removeNulls && null === $v) {
                unset($data[$k]);
                continue;
            }
            if (isset($meta['types'][$k])) {
                switch(true) {
                    case 'DateTime' === substr($meta['types'][$k], 0, 8):
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
        $embeddedReferences = $this->getEmbeddedReferencesByClass($doc);
        $embeddedReferenceLists = $this->getEmbeddedReferenceListsByClass($doc);
        $types = $this->getTypesByClass($doc);

        if (isset($data['_id']) && !isset($data['id'])) {
            $data['id'] = (string)$data['_id'];
            unset($data['_id']);
        }

        foreach($data as $k => $v) {
            if (isset($embeddedReferences[$k])) {
                $v = $this->mutateArrayToObject($v, $embeddedReferences[$k]['class']);
            }
            if (isset($embeddedReferenceLists[$k])) {
                $v = []; // @todo
            }
            if (isset($types[$k])) {
                switch(true) {
                    case 'DateTime' === substr($types[$k], 0, 8):
                        $data = $this->revertDocumentMongoDateWithTimeZoneFieldToDateTime($data, $k);
                        $v = $data[$k];
                }
                $doc->$k = $v;
            }
        }

        return $doc;
    }





    /**
     * @param array $definition
     * @param mixed $entireDoc
     *
     * @return string
     */
    protected function generateValue($definition, $entireDoc)
    {
        unset($entireDoc);

        switch($definition['type']) {
            case 'sha1': return sha1(md5(rand(0, 1000) . microtime(true) . rand(rand(0, 100), 10000)));
            default:
                throw $this->createException(500, "Unsupported generate type '%s'", $definition['type']);
                return $this;
        }
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
     * @move
     */
    protected function convert2($data)
    {
        foreach($data as $k => $v) {
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
        foreach(array_keys($doc) as $k) {
            if (!isset($doc[$k])) continue; // if key was removed by previous iteration
            if ('Date' === substr($k, -4)) {
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
     */
    protected function convertDataDateTimeFieldToMongoDateWithTimeZone($data, $fieldName)
    {
        if (!isset($data[$fieldName])) throw $this->createException(412, "Missing date time field '%s'", $fieldName);

        if (null !== $data[$fieldName] && !$data[$fieldName] instanceof \DateTime)
            throw $this->createException(412, "Field '%s' must be a valid DateTime", $fieldName);

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
     */
    protected function revertDocumentMongoDateWithTimeZoneFieldToDateTime($doc, $fieldName)
    {
        if (!isset($doc[$fieldName])) throw $this->createException(412, "Missing mongo date field '%s'", $fieldName);

        if (!isset($doc[sprintf('%s_tz', $fieldName)])) {
            $doc[sprintf('%s_tz', $fieldName)] = date_default_timezone_get();
        }

        if (!$doc[$fieldName] instanceof \MongoDate)
            throw $this->createException(412, "Field '%s' must be a valid MongoDate", $fieldName);

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