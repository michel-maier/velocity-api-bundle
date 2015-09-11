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

use Zend\Code\Generator\ClassGenerator;
use Zend\Code\Generator\DocBlock\Tag\ParamTag;
use Zend\Code\Generator\DocBlock\Tag\ReturnTag;
use Zend\Code\Generator\DocBlock\Tag\ThrowsTag;
use Zend\Code\Generator\DocBlockGenerator;
use Zend\Code\Generator\FileGenerator;
use Zend\Code\Generator\MethodGenerator;
use Zend\Code\Generator\ParameterGenerator;
use Velocity\Bundle\ApiBundle\Traits\ServiceTrait;
use Velocity\Bundle\ApiBundle\Traits\ServiceAware;
use Zend\Code\Generator\ValueGenerator;

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
        $zFile = $this->createFile();
        $zFile->setClass($this->createClass($name, $definition));

        return $zFile;
    }
    /**
     * @param array $definition
     *
     * @return FileGenerator
     */
    public function createFile($definition = [])
    {
        unset($definition);

        return new FileGenerator();
    }
    /**
     * @param string $name
     * @param array  $definition
     *
     * @return ClassGenerator
     */
    public function createClass($name, $definition = [])
    {
        $definition += ['methods' => []];

        $zMethods = [];

        foreach ($definition['methods'] as $methodName => $method) {
            $params = isset($method['params']) ? $method['params'] : [];
            unset($method['params']);
            $zMethods[] = $this->createMethod($methodName, $method + $params + $definition);
        }

        list($namespace, $baseName) = $this->explodeClassNamespace($name);

        return new ClassGenerator($baseName, $namespace, null, null, null, [], $zMethods);
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

        $this->$buildTypeMethod($zMethod, $definition);

        return $zMethod;
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
    protected function buildCrudGetMethod(MethodGenerator $zMethod, $definition = [])
    {
        $zMethod->setDocBlock(new DocBlockGenerator(
            sprintf('Return the specified %s', $definition['serviceName']),
            null,
            [
                new ParamTag('id', ['mixed'], sprintf('ID of the %s', $definition['serviceName'])),
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
            sprintf('return $this->getSdk()->get(sprintf(\'/%ss/%%s\', $id), $fields, $options);', $definition['serviceName'])
        );
    }
    /**
     * @param MethodGenerator $zMethod
     * @param array           $definition
     */
    protected function buildCrudCreateMethod(MethodGenerator $zMethod, $definition = [])
    {
        $zMethod->setDocBlock(new DocBlockGenerator(
            sprintf('Create a new %s', $definition['serviceName']),
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
            sprintf('return $this->getSdk()->create(\'/%ss\', $data, $options);', $definition['serviceName'])
        );
    }
    /**
     * @param MethodGenerator $zMethod
     * @param array           $definition
     */
    protected function buildCrudDeleteMethod(MethodGenerator $zMethod, $definition = [])
    {
        $zMethod->setDocBlock(new DocBlockGenerator(
            sprintf('Delete the specified %s', $definition['serviceName']),
            null,
            [
                new ParamTag('id', ['mixed'], sprintf('ID of the %s', $definition['serviceName'])),
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
            sprintf('return $this->getSdk()->delete(sprintf(\'/%ss/%%s\', $id), $options);', $definition['serviceName'])
        );
    }
    /**
     * @param MethodGenerator $zMethod
     * @param array           $definition
     */
    protected function buildCrudPurgeMethod(MethodGenerator $zMethod, $definition = [])
    {
        $zMethod->setDocBlock(new DocBlockGenerator(
            sprintf('Purge all %ss', $definition['serviceName']),
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
            sprintf('return $this->getSdk()->purge(\'/%ss\', $criteria, $options);', $definition['serviceName'])
        );
    }
    /**
     * @param MethodGenerator $zMethod
     * @param array           $definition
     */
    protected function buildCrudUpdateMethod(MethodGenerator $zMethod, $definition = [])
    {
        $zMethod->setDocBlock(new DocBlockGenerator(
            sprintf('Update the specified %s', $definition['serviceName']),
            null,
            [
                new ParamTag('id', ['mixed'], sprintf('ID of the %s', $definition['serviceName'])),
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
            sprintf('return $this->getSdk()->update(sprintf(\'/%ss/%%s\', $id), $data, $options);', $definition['serviceName'])
        );
    }
    /**
     * @param MethodGenerator $zMethod
     * @param array           $definition
     */
    protected function buildCrudUpdatePropertyMethod(MethodGenerator $zMethod, $definition = [])
    {
        $zMethod->setDocBlock(new DocBlockGenerator(
            sprintf('Update %s of the specified %s', $definition['property'], $definition['serviceName']),
            null,
            [
                new ParamTag('id', ['mixed'], sprintf('ID of the %s', $definition['serviceName'])),
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
            sprintf('return $this->getSdk()->update(sprintf(\'/%ss/%%s/%s\', $id), $data, $options);', $definition['serviceName'], $definition['property'])
        );
    }
    /**
     * @param MethodGenerator $zMethod
     * @param array           $definition
     */
    protected function buildCrudFindMethod(MethodGenerator $zMethod, $definition = [])
    {
        $zMethod->setDocBlock(new DocBlockGenerator(
            sprintf('Find %ss', $definition['serviceName']),
            null,
            [
                new ParamTag('criteria', ['array'], sprintf('Optional criteria to filter %ss', $definition['serviceName'])),
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
            sprintf('return $this->getSdk()->find(\'/%ss\', $criteria, $fields, $limit, $offset, $sorts, $options);', $definition['serviceName'])
        );
    }
    /**
     * @param MethodGenerator $zMethod
     * @param array           $definition
     */
    protected function buildCrudFindPageMethod(MethodGenerator $zMethod, $definition = [])
    {
        $zMethod->setDocBlock(new DocBlockGenerator(
            sprintf('Find a page of %ss', $definition['serviceName']),
            null,
            [
                new ParamTag('criteria', ['array'], sprintf('Optional criteria to filter %ss', $definition['serviceName'])),
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
            sprintf('return $this->getSdk()->findPage(\'/%ss\', $criteria, $fields, $page, $size, $sorts, $options);', $definition['serviceName'])
        );
    }
    /**
     * @param MethodGenerator $zMethod
     * @param array           $definition
     */
    protected function buildGetMethod(MethodGenerator $zMethod, $definition = [])
    {
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
