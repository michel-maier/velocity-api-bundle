<?php

/*
 * This file is part of the VELOCITY package.
 *
 * (c) PHPPRO <opensource@phppro.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Velocity\Bundle\ApiBundle\CodeGenerator;

use Zend\Code\Generator\ValueGenerator;
use Zend\Code\Generator\MethodGenerator;
use Zend\Code\Generator\DocBlockGenerator;
use Zend\Code\Generator\ParameterGenerator;
use Zend\Code\Generator\DocBlock\Tag\ParamTag;
use Zend\Code\Generator\DocBlock\Tag\ReturnTag;
use Zend\Code\Generator\DocBlock\Tag\ThrowsTag;
use Velocity\Bundle\ApiBundle\Annotation as Velocity;

/**
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
class SdkMethodsCodeGenerator
{
    /**
     * @param MethodGenerator $zMethod
     * @param array           $definition
     *
     * @Velocity\CodeGeneratorMethodType("sdk.construct")
     */
    public function generateSdkConstructMethod(MethodGenerator $zMethod, array $definition = [])
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
     *
     * @Velocity\CodeGeneratorMethodType("sdk.service.test.testConstruct")
     */
    public function generateSdkServiceTestTestConstructMethod(MethodGenerator $zMethod, array $definition = [])
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
     *
     * @Velocity\CodeGeneratorMethodType("crud.get")
     */
    public function generateCrudGetMethod(MethodGenerator $zMethod, $definition = [])
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
     *
     * @Velocity\CodeGeneratorMethodType("crud.getBy")
     */
    public function generateCrudGetByMethod(MethodGenerator $zMethod, $definition = [])
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
     *
     * @Velocity\CodeGeneratorMethodType("crud.getPropertyBy")
     */
    public function generateCrudGetPropertyByMethod(MethodGenerator $zMethod, $definition = [])
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
     *
     * @Velocity\CodeGeneratorMethodType("crud.getPropertyPathBy")
     */
    public function generateCrudGetPropertyPathByMethod(MethodGenerator $zMethod, $definition = [])
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
            sprintf('return $this->getSdk()->getPath(sprintf(\'%s\', $%s), [], [\'format\' => \'%s\'] + $options);', $definition['route'], $definition['field'], $definition['format'])
        );
    }
    /**
     * @param MethodGenerator $zMethod
     * @param array           $definition
     *
     * @Velocity\CodeGeneratorMethodType("crud.create")
     */
    public function generateCrudCreateMethod(MethodGenerator $zMethod, $definition = [])
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
     *
     * @Velocity\CodeGeneratorMethodType("crud.sub.createBy")
     */
    public function generateCrudSubCreateByMethod(MethodGenerator $zMethod, $definition = [])
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
     *
     * @Velocity\CodeGeneratorMethodType("crud.delete")
     */
    public function generateCrudDeleteMethod(MethodGenerator $zMethod, $definition = [])
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
     *
     * @Velocity\CodeGeneratorMethodType("crud.purge")
     */
    public function generateCrudPurgeMethod(MethodGenerator $zMethod, $definition = [])
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
     *
     * @Velocity\CodeGeneratorMethodType("crud.update")
     */
    public function generateCrudUpdateMethod(MethodGenerator $zMethod, $definition = [])
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
     *
     * @Velocity\CodeGeneratorMethodType("crud.updateBy")
     */
    public function generateCrudUpdateByMethod(MethodGenerator $zMethod, $definition = [])
    {
        $definition += ['field' => 'id'];
        $definition['route'] = str_replace('{'.$definition['field'].'}', '%s', $definition['route']);

        $zMethod->setDocBlock(new DocBlockGenerator(
            sprintf('Update a %s by %s', $definition['type'], $definition['field']),
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
            sprintf('return $this->getSdk()->update(sprintf(\'%s\', $%s), $data, $options);', $definition['route'], $definition['field'])
        );
    }
    /**
     * @param MethodGenerator $zMethod
     * @param array           $definition
     *
     * @Velocity\CodeGeneratorMethodType("crud.updateProperty")
     */
    public function generateCrudUpdatePropertyMethod(MethodGenerator $zMethod, $definition = [])
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
     *
     * @Velocity\CodeGeneratorMethodType("crud.find")
     */
    public function generateCrudFindMethod(MethodGenerator $zMethod, $definition = [])
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
     *
     * @Velocity\CodeGeneratorMethodType("crud.sub.findBy")
     */
    public function generateCrudSubFindByMethod(MethodGenerator $zMethod, $definition = [])
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
     *
     * @Velocity\CodeGeneratorMethodType("crud.findPage")
     */
    public function generateCrudFindPageMethod(MethodGenerator $zMethod, $definition = [])
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
}
