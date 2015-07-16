<?php

namespace Velocity\Bundle\ApiBundle\Service;

use Velocity\Bundle\ApiBundle\Traits\ServiceTrait;

class MetaDataService
{
    use ServiceTrait;
    /**
     * @var array
     */
    protected $classes;
    /**
     *
     */
    public function __construct()
    {
        $this->classes = [];
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
}