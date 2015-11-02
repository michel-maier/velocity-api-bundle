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

use Velocity\Core\Traits\ServiceTrait;
use Zend\Code\Generator\FileGenerator;
use Zend\Code\Generator\ClassGenerator;
use Zend\Code\Generator\MethodGenerator;
use Zend\Code\Generator\PropertyGenerator;
use Zend\Code\Generator\DocBlockGenerator;
use Zend\Code\Generator\PropertyValueGenerator;
use Zend\Code\Generator\DocBlock\Tag\GenericTag;
use Velocity\Bundle\ApiBundle\Traits\ServiceAware;

/**
 * Code Generator Service.
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
class CodeGeneratorService
{
    use ServiceTrait;
    use ServiceAware\CallableServiceAwareTrait;
    /**
     * @param CallableService $callableService
     */
    public function __construct(CallableService $callableService)
    {
        $this->setCallableService($callableService);
    }
    /**
     * @param string $name
     * @param array  $definition
     *
     * @return FileGenerator
     */
    public function createClassFile($name, $definition = [])
    {
        list($namespace) = $this->explodeClassNamespace($name);

        $zFile = $this->createFile(['namespace' => $namespace] + $definition);
        $zFile->setClass($this->createClass($name, ['zFile' => $zFile, 'namespace' => false] + $definition));

        return $zFile;
    }
    /**
     * @param array $definition
     *
     * @return FileGenerator
     */
    public function createFile($definition = [])
    {
        $zFile = new FileGenerator();

        if (isset($definition['uses'])) {
            $zFile->setUses($definition['uses']);
        }

        if (isset($definition['namespace'])) {
            $zFile->setNamespace($definition['namespace']);
        }

        return $zFile;
    }
    /**
     * @param string $name
     * @param array  $definition
     *
     * @return ClassGenerator
     */
    public function createClass($name, $definition = [])
    {
        $definition += ['methods' => [], 'uses' => [], 'properties' => []];

        $zMethods    = [];
        $zProperties = [];

        foreach ($definition['methods'] as $methodName => $method) {
            $zMethods[] = $this->createMethod($methodName, $method + $definition);
        }

        foreach ($definition['properties'] as $propertyName => $property) {
            $params = isset($property['params']) ? $property['params'] : [];
            unset($property['params']);
            $zProperties[] = $this->createProperty($propertyName, $property + $params + $definition);
        }

        list($namespace, $baseName) = $this->explodeClassNamespace($name);

        if (isset($definition['namespace']) && false === $definition['namespace']) {
            $namespace = null;
        }

        $parent = null;

        if (isset($definition['parent'])) {
            $parent = $definition['parent'];
        }

        $zClass = new ClassGenerator($baseName, $namespace, null, $parent, null, $zProperties, $zMethods);

        if (isset($definition['traits'])) {
            $zClass->addTraits($definition['traits']);
        }

        return $zClass;
    }
    /**
     * @param string $name
     * @param array  $definition
     *
     * @return MethodGenerator
     *
     * @throws \Exception
     */
    public function createMethod($name, $definition = [])
    {
        $definition += ['type' => null, 'params' => []];

        $zMethod = new MethodGenerator($name, []);

        if (null !== $definition['type']) {
            $type = $definition['type'];
            unset($definition['type']);
            $definition += $definition['params'];
            unset($definition['params']);
            $this->getCallableService()->executeByType('methodType', $type, [$zMethod, $definition]);
        }

        return $zMethod;
    }
    /**
     * @param string   $name
     * @param callable $callable
     *
     * @return $this
     */
    public function registerMethodType($name, $callable)
    {
        $this->getCallableService()->registerByType('methodType', $name, $callable);

        return $this;
    }
    /**
     * @param string $name
     * @param array  $definition
     *
     * @return PropertyGenerator
     *
     * @throws \Exception
     */
    public function createProperty($name, $definition = [])
    {
        $definition += ['type' => 'basic', 'visibility' => 'public'];

        switch ($definition['visibility']) {
            case 'private':
                $visibility = PropertyGenerator::FLAG_PRIVATE;
                break;
            case 'protected':
                $visibility = PropertyGenerator::FLAG_PROTECTED;
                break;
            default:
            case 'public':
                $visibility = PropertyGenerator::FLAG_PUBLIC;
                break;
        }

        $zProperty = new PropertyGenerator($name, isset($definition['default']) ? $definition['default'] : new PropertyValueGenerator(), $visibility);

        $buildTypeProperty = 'build'.ucfirst(str_replace(' ', '', ucwords(str_replace('.', ' ', $definition['type'])))).'Property';

        if (!method_exists($this, $buildTypeProperty)) {
            throw $this->createMalformedException("Unknown property type '%s'", $definition['type']);
        }

        unset($definition['type']);

        $this->$buildTypeProperty($zProperty, $definition);

        if (isset($definition['cast'])) {
            if (null === $zProperty->getDocBlock()) {
                $zProperty->setDocBlock(new DocBlockGenerator());
            }
            $zProperty->getDocBlock()->setTag(new GenericTag('var', is_array($definition['cast']) ? join('|', $definition['cast']) : $definition['cast'], $name));
        }

        return $zProperty;
    }
    /**
     * @param PropertyGenerator $zMethod
     * @param array           $definition
     */
    protected function buildBasicProperty(PropertyGenerator $zMethod, $definition = [])
    {
    }
    /**
     * @param string $name
     *
     * @return array
     */
    protected function explodeClassNamespace($name)
    {
        $pos = strrpos($name, '\\');

        if (false === $pos) {
            return [null, $name];
        }

        return [substr($name, 0, $pos), substr($name, $pos + 1)];
    }
}
