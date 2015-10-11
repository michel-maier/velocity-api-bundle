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
use Zend\Code\Generator\ValueGenerator;
use Zend\Code\Generator\MethodGenerator;
use Zend\Code\Generator\PropertyGenerator;
use Zend\Code\Generator\DocBlockGenerator;
use Zend\Code\Generator\ParameterGenerator;
use Zend\Code\Generator\DocBlock\Tag\ParamTag;
use Zend\Code\Generator\PropertyValueGenerator;
use Zend\Code\Generator\DocBlock\Tag\ReturnTag;
use Zend\Code\Generator\DocBlock\Tag\ThrowsTag;
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
        $zFile->setClass($this->createClass($name, ['namespace' => false] + $definition));

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
        $definition += ['type' => 'basic'];

        $zMethod = new MethodGenerator($name, []);

        $buildTypeMethod = 'build'.ucfirst(str_replace(' ', '', ucwords(str_replace('.', ' ', $definition['type'])))).'Method';

        if (!method_exists($this, $buildTypeMethod)) {
            throw $this->createMalformedException("Unknown method type '%s'", $definition['type']);
        }

        unset($definition['type']);

        if (isset($definition['params'])) {
            $definition += $definition['params'];
        }

        unset($definition['params']);

        $this->$buildTypeMethod($zMethod, $definition);

        return $zMethod;
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
     * @param MethodGenerator $zMethod
     * @param array           $definition
     */
    protected function buildBasicMethod(MethodGenerator $zMethod, $definition = [])
    {
    }
    /**
     * @param MethodGenerator $zMethod
     * @param array           $definition
     */
    protected function buildSdkConstructMethod(MethodGenerator $zMethod, $definition = [])
    {
        $zMethod->setDocBlock(new DocBlockGenerator(
            sprintf('Construct a %s service', $definition['serviceName']),
            null,
            [
                new ParamTag('sdk', ['SdkInterface'], 'Underlying SDK'),
            ]
        ));
        $zMethod->setParameters([
            new ParameterGenerator('sdk', 'SdkInterface'),
        ]);
        $zMethod->setBody('$this->setSdk($sdk);');
    }
    /**
     * @param MethodGenerator $zMethod
     * @param array           $definition
     */
    protected function buildSdkServiceTestTestConstructMethod(MethodGenerator $zMethod, $definition = [])
    {
        $zMethod->setDocBlock(new DocBlockGenerator(
            sprintf('Test constructor for service %s', $definition['serviceName']),
            null,
            []
        ));
        $zMethod->setParameters([]);
        $zMethod->setBody(
            '$this->sdk = $this->getMock(\'Phppro\\\\Sdk\\\\Sdk\', [], [], \'\', false);'."\n".sprintf('$this->assertNotNull(new %sService($this->sdk));', ucfirst($definition['serviceName']))
        );
    }
    /**
     * @param MethodGenerator $zMethod
     * @param array           $definition
     */
    protected function buildCrudGetMethod(MethodGenerator $zMethod, $definition = [])
    {
        $definition['route'] = str_replace('{id}', '%s', $definition['route']);

        $zMethod->setDocBlock(new DocBlockGenerator(
            sprintf('Return the specified %s', $definition['type']),
            null,
            [
                new ParamTag('id', ['mixed'], sprintf('ID of the %s', $definition['type'])),
                new ParamTag('fields', ['array'], 'List of fields to retrieve'),
                new ParamTag('options', ['array'], 'Options'),
                new ReturnTag(['array']),
                new ThrowsTag(['\\Exception'], 'if an error occured'),
            ]
        ));
        $zMethod->setParameters([
            new ParameterGenerator('id', 'string'),
            new ParameterGenerator('fields', 'array', []),
            new ParameterGenerator('options', 'array', []),
        ]);
        $zMethod->setBody(
            sprintf('return $this->getSdk()->get(sprintf(\'%s\', $id), $fields, $options);', $definition['route'])
        );
    }
    /**
     * @param MethodGenerator $zMethod
     * @param array           $definition
     */
    protected function buildCrudGetByMethod(MethodGenerator $zMethod, $definition = [])
    {
        $definition += ['field' => 'id'];
        $definition['route'] = str_replace('{'.$definition['field'].'}', '%s', $definition['route']);

        $zMethod->setDocBlock(new DocBlockGenerator(
            sprintf('Return the specified %s by %s', $definition['type'], $definition['field']),
            null,
            [
                new ParamTag($definition['field'], ['mixed'], sprintf(ucfirst($definition['field']).' of the %s', $definition['type'])),
                new ParamTag('fields', ['array'], 'List of fields to retrieve'),
                new ParamTag('options', ['array'], 'Options'),
                new ReturnTag(['array']),
                new ThrowsTag(['\\Exception'], 'if an error occured'),
            ]
        ));
        $zMethod->setParameters([
            new ParameterGenerator($definition['field'], 'string'),
            new ParameterGenerator('fields', 'array', []),
            new ParameterGenerator('options', 'array', []),
        ]);
        $zMethod->setBody(
            sprintf('return $this->getSdk()->get(sprintf(\'%s\', $%s), $fields, $options);', $definition['route'], $definition['field'])
        );
    }
    /**
     * @param MethodGenerator $zMethod
     * @param array           $definition
     */
    protected function buildCrudGetPropertyByMethod(MethodGenerator $zMethod, $definition = [])
    {
        $definition += ['field' => 'id', 'property' => 'id'];
        $definition['route'] = str_replace(['{'.$definition['field'].'}', '{'.$definition['property'].'}'], '%s', $definition['route']);

        $zMethod->setDocBlock(new DocBlockGenerator(
            sprintf('Return the specified %s %s by %s', $definition['type'], $definition['property'], $definition['field']),
            null,
            [
                new ParamTag($definition['field'], ['mixed'], sprintf(ucfirst($definition['field']).' of the %s', $definition['type'])),
                new ParamTag('options', ['array'], 'Options'),
                new ReturnTag(['mixed']),
                new ThrowsTag(['\\Exception'], 'if an error occured'),
            ]
        ));
        $zMethod->setParameters([
            new ParameterGenerator($definition['field'], 'string'),
            new ParameterGenerator('options', 'array', []),
        ]);
        $zMethod->setBody(
            sprintf('return $this->getSdk()->get(sprintf(\'%s\', $%s), [], $options);', $definition['route'], $definition['field'])
        );
    }
    /**
     * @param MethodGenerator $zMethod
     * @param array           $definition
     */
    protected function buildCrudGetPropertyPathByMethod(MethodGenerator $zMethod, $definition = [])
    {
        $definition += ['field' => 'id', 'property' => 'id'];
        $definition['route'] = str_replace(['{'.$definition['field'].'}', '{'.$definition['property'].'}'], '%s', $definition['route']);

        $zMethod->setDocBlock(new DocBlockGenerator(
            sprintf('Return the local path of the file containing the specified %s %s by %s', $definition['type'], $definition['property'], $definition['field']),
            null,
            [
                new ParamTag($definition['field'], ['mixed'], sprintf(ucfirst($definition['field']).' of the %s', $definition['type'])),
                new ParamTag('options', ['array'], 'Options'),
                new ReturnTag(['mixed']),
                new ThrowsTag(['\\Exception'], 'if an error occured'),
            ]
        ));
        $zMethod->setParameters([
            new ParameterGenerator($definition['field'], 'string'),
            new ParameterGenerator('options', 'array', []),
        ]);
        $zMethod->setBody(
            sprintf('return $this->getSdk()->getPath(sprintf(\'%s\', $%s), [], [\'raw\' => true] + $options);', $definition['route'], $definition['field'])
        );
    }
    /**
     * @param MethodGenerator $zMethod
     * @param array           $definition
     */
    protected function buildCrudCreateMethod(MethodGenerator $zMethod, $definition = [])
    {
        $zMethod->setDocBlock(new DocBlockGenerator(
            sprintf('Create a new %s', $definition['type']),
            null,
            [
                new ParamTag('data', ['array'], 'Data to store'),
                new ParamTag('options', ['array'], 'Options'),
                new ReturnTag(['array']),
                new ThrowsTag(['\\Exception'], 'if an error occured'),
            ]
        ));
        $zMethod->setParameters([
            new ParameterGenerator('data', 'array', []),
            new ParameterGenerator('options', 'array', []),
        ]);
        $zMethod->setBody(
            sprintf('return $this->getSdk()->create(\'%s\', $data, $options);', $definition['route'])
        );
    }
    /**
     * @param MethodGenerator $zMethod
     * @param array           $definition
     */
    protected function buildCrudSubCreateByMethod(MethodGenerator $zMethod, $definition = [])
    {
        $definition += ['field' => 'id', 'subType' => 'subType'];
        $definition['route'] = str_replace('{'.$definition['field'].'}', '%s', $definition['route']);

        $zMethod->setDocBlock(new DocBlockGenerator(
            sprintf('Create a new %s %s by %s', $definition['type'], $definition['subType'], $definition['field']),
            null,
            [
                new ParamTag($definition['field'], ['mixed'], sprintf('%s of the %s', ucfirst($definition['field']), $definition['type'])),
                new ParamTag('data', ['array'], 'Data to store'),
                new ParamTag('options', ['array'], 'Options'),
                new ReturnTag(['array']),
                new ThrowsTag(['\\Exception'], 'if an error occured'),
            ]
        ));
        $zMethod->setParameters([
            new ParameterGenerator($definition['field'], 'mixed'),
            new ParameterGenerator('data', 'array', []),
            new ParameterGenerator('options', 'array', []),
        ]);
        $zMethod->setBody(
            sprintf('return $this->getSdk()->create(sprintf(\'%s\', $%s), $data, $options);', $definition['route'], $definition['field'])
        );
    }
    /**
     * @param MethodGenerator $zMethod
     * @param array           $definition
     */
    protected function buildCrudDeleteMethod(MethodGenerator $zMethod, $definition = [])
    {
        $definition['route'] = str_replace('{id}', '%s', $definition['route']);

        $zMethod->setDocBlock(new DocBlockGenerator(
            sprintf('Delete the specified %s', $definition['type']),
            null,
            [
                new ParamTag('id', ['mixed'], sprintf('ID of the %s', $definition['type'])),
                new ParamTag('options', ['array'], 'Options'),
                new ReturnTag(['array']),
                new ThrowsTag(['\\Exception'], 'if an error occured'),
            ]
        ));
        $zMethod->setParameters([
            new ParameterGenerator('id', 'string'),
            new ParameterGenerator('options', 'array', []),
        ]);
        $zMethod->setBody(
            sprintf('return $this->getSdk()->delete(sprintf(\'%s\', $id), $options);', $definition['route'])
        );
    }
    /**
     * @param MethodGenerator $zMethod
     * @param array           $definition
     */
    protected function buildCrudPurgeMethod(MethodGenerator $zMethod, $definition = [])
    {
        $zMethod->setDocBlock(new DocBlockGenerator(
            sprintf('Purge all %ss', $definition['type']),
            null,
            [
                new ParamTag('criteria', ['array'], 'Optional criteria to filter deleteds'),
                new ParamTag('options', ['array'], 'Options'),
                new ReturnTag(['array']),
                new ThrowsTag(['\\Exception'], 'if an error occured'),
            ]
        ));
        $zMethod->setParameters([
            new ParameterGenerator('criteria', 'array', []),
            new ParameterGenerator('options', 'array', []),
        ]);
        $zMethod->setBody(
            sprintf('return $this->getSdk()->purge(\'%s\', $criteria, $options);', $definition['route'])
        );
    }
    /**
     * @param MethodGenerator $zMethod
     * @param array           $definition
     */
    protected function buildCrudUpdateMethod(MethodGenerator $zMethod, $definition = [])
    {
        $definition['route'] = str_replace('{id}', '%s', $definition['route']);

        $zMethod->setDocBlock(new DocBlockGenerator(
            sprintf('Update the specified %s', $definition['type']),
            null,
            [
                new ParamTag('id', ['mixed'], sprintf('ID of the %s', $definition['type'])),
                new ParamTag('data', ['array'], 'Data to update'),
                new ParamTag('options', ['array'], 'Options'),
                new ReturnTag(['array']),
                new ThrowsTag(['\\Exception'], 'if an error occured'),
            ]
        ));
        $zMethod->setParameters([
            new ParameterGenerator('id', 'string'),
            new ParameterGenerator('data', 'array', []),
            new ParameterGenerator('options', 'array', []),
        ]);
        $zMethod->setBody(
            sprintf('return $this->getSdk()->update(sprintf(\'%s\', $id), $data, $options);', $definition['route'])
        );
    }
    /**
     * @param MethodGenerator $zMethod
     * @param array           $definition
     */
    protected function buildCrudUpdatePropertyMethod(MethodGenerator $zMethod, $definition = [])
    {
        $definition['route'] = str_replace('{id}', '%s', $definition['route']);

        $zMethod->setDocBlock(new DocBlockGenerator(
            sprintf('Update %s of the specified %s', $definition['property'], $definition['type']),
            null,
            [
                new ParamTag('id', ['mixed'], sprintf('ID of the %s', $definition['type'])),
                new ParamTag('data', ['mixed'], sprintf('Value for the %s', $definition['property'])),
                new ParamTag('options', ['array'], 'Options'),
                new ReturnTag(['array']),
                new ThrowsTag(['\\Exception'], 'if an error occured'),
            ]
        ));
        $zMethod->setParameters([
            new ParameterGenerator('id', 'string'),
            new ParameterGenerator('data', 'mixed'),
            new ParameterGenerator('options', 'array', []),
        ]);
        $zMethod->setBody(
            sprintf('return $this->getSdk()->update(sprintf(\'%s\', $id), $data, $options);', $definition['route'])
        );
    }
    /**
     * @param MethodGenerator $zMethod
     * @param array           $definition
     */
    protected function buildCrudFindMethod(MethodGenerator $zMethod, $definition = [])
    {
        $zMethod->setDocBlock(new DocBlockGenerator(
            sprintf('Find %ss', $definition['type']),
            null,
            [
                new ParamTag('criteria', ['array'], sprintf('Optional criteria to filter %ss', $definition['type'])),
                new ParamTag('fields', ['array'], 'Optional fields to retrieve'),
                new ParamTag('limit', ['int'], 'Optional limit'),
                new ParamTag('offset', ['int'], 'Optional offset'),
                new ParamTag('sorts', ['array'], 'Optional sorts'),
                new ParamTag('options', ['array'], 'Options'),
                new ReturnTag(['array']),
                new ThrowsTag(['\\Exception'], 'if an error occured'),
            ]
        ));
        $zMethod->setParameters([
            new ParameterGenerator('criteria', 'array', []),
            new ParameterGenerator('fields', 'array', []),
            new ParameterGenerator('limit', 'int', new ValueGenerator(null)),
            new ParameterGenerator('offset', 'int', 0),
            new ParameterGenerator('sorts', 'array', []),
            new ParameterGenerator('options', 'array', []),
        ]);
        $zMethod->setBody(
            sprintf('return $this->getSdk()->find(\'%s\', $criteria, $fields, $limit, $offset, $sorts, $options);', $definition['route'])
        );
    }
    /**
     * @param MethodGenerator $zMethod
     * @param array           $definition
     */
    protected function buildCrudSubFindByMethod(MethodGenerator $zMethod, $definition = [])
    {
        $definition['route'] = str_replace('{'.$definition['field'].'}', '%s', $definition['route']);

        $zMethod->setDocBlock(new DocBlockGenerator(
            sprintf('Find %s %ss by %s', $definition['type'], $definition['subType'], $definition['field']),
            null,
            [
                new ParamTag($definition['field'], ['mixed'], sprintf(ucfirst($definition['field']).' of the %s', $definition['type'])),
                new ParamTag('criteria', ['array'], sprintf('Optional criteria to filter %ss', $definition['type'])),
                new ParamTag('fields', ['array'], 'Optional fields to retrieve'),
                new ParamTag('limit', ['int'], 'Optional limit'),
                new ParamTag('offset', ['int'], 'Optional offset'),
                new ParamTag('sorts', ['array'], 'Optional sorts'),
                new ParamTag('options', ['array'], 'Options'),
                new ReturnTag(['array']),
                new ThrowsTag(['\\Exception'], 'if an error occured'),
            ]
        ));
        $zMethod->setParameters([
            new ParameterGenerator($definition['field'], 'mixed'),
            new ParameterGenerator('criteria', 'array', []),
            new ParameterGenerator('fields', 'array', []),
            new ParameterGenerator('limit', 'int', new ValueGenerator(null)),
            new ParameterGenerator('offset', 'int', 0),
            new ParameterGenerator('sorts', 'array', []),
            new ParameterGenerator('options', 'array', []),
        ]);
        $zMethod->setBody(
            sprintf('return $this->getSdk()->find(sprintf(\'%s\', $%s), $criteria, $fields, $limit, $offset, $sorts, $options);', $definition['route'], $definition['field'])
        );
    }
    /**
     * @param MethodGenerator $zMethod
     * @param array           $definition
     */
    protected function buildCrudFindPageMethod(MethodGenerator $zMethod, $definition = [])
    {
        $zMethod->setDocBlock(new DocBlockGenerator(
            sprintf('Find a page of %ss', $definition['type']),
            null,
            [
                new ParamTag('criteria', ['array'], sprintf('Optional criteria to filter %ss', $definition['type'])),
                new ParamTag('fields', ['array'], 'Optional fields to retrieve'),
                new ParamTag('page', ['int'], 'Rank of the page to retrieve'),
                new ParamTag('size', ['int'], 'Size of pages'),
                new ParamTag('sorts', ['array'], 'Optional sorts'),
                new ParamTag('options', ['array'], 'Options'),
                new ReturnTag(['array']),
                new ThrowsTag(['\\Exception'], 'if an error occured'),
            ]
        ));
        $zMethod->setParameters([
            new ParameterGenerator('criteria', 'array', []),
            new ParameterGenerator('fields', 'array', []),
            new ParameterGenerator('page', 'int', 0),
            new ParameterGenerator('size', 'int', 10),
            new ParameterGenerator('sorts', 'array', []),
            new ParameterGenerator('options', 'array', []),
        ]);
        $zMethod->setBody(
            sprintf('return $this->getSdk()->findPage(\'%s\', $criteria, $fields, $page, $size, $sorts, $options);', $definition['route'])
        );
    }
    /**
     * @param MethodGenerator $zMethod
     * @param array           $definition
     */
    protected function buildGetMethod(MethodGenerator $zMethod, $definition = [])
    {
        unset($definition);

        $zMethod->setParameters([]);
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
