<?php

/*
 * This file is part of the VELOCITY package.
 *
 * (c) PHPPRO <opensource@phppro.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Velocity\Bundle\ApiBundle\DependencyInjection\Compiler;

use JMS\Serializer\Annotation\Type;
use Velocity\Bundle\ApiBundle\Annotation\Id;
use Doctrine\Common\Annotations\AnnotationReader;
use Velocity\Bundle\ApiBundle\Annotation\Refresh;
use Velocity\Bundle\ApiBundle\Annotation\Generated;
use Symfony\Component\DependencyInjection\Definition;
use Velocity\Bundle\ApiBundle\Annotation\EmbeddedReference;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Velocity\Bundle\ApiBundle\Annotation\EmbeddedReferenceList;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

/**
 * Annotation Compiler Pass.
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
class AnnotationCompilerPass implements CompilerPassInterface
{
    /**
     * Process the compiler pass.
     *
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        $reader = new AnnotationReader();

        $classes = $container->hasParameter('app.models')
            ? $container->getParameter('app.models')
            : [];

        if (!is_array($classes)) {
            $classes = [];
        }

        $metaDataDefinition = $container->getDefinition('api.metadata');

        foreach($classes as $class) {
            $rClass = new \ReflectionClass($class);
            foreach($rClass->getProperties(\ReflectionProperty::IS_PUBLIC) as $rProperty) {
                foreach($reader->getPropertyAnnotations($rProperty) as $annotation) {
                    switch(true) {
                        case $annotation instanceof EmbeddedReference:
                            $this->registerEmbeddedReference(
                                $metaDataDefinition, $class, $rProperty->getName(), $annotation
                            );
                            break;
                        case $annotation instanceof EmbeddedReferenceList:
                            $this->registerEmbeddedReferenceList(
                                $metaDataDefinition, $class, $rProperty->getName(), $annotation
                            );
                            break;
                        case $annotation instanceof Refresh:
                            $this->registerRefresh(
                                $metaDataDefinition, $class, $rProperty->getName(), $annotation
                            );
                            break;
                        case $annotation instanceof Generated:
                            $this->registerGenerated(
                                $metaDataDefinition, $class, $rProperty->getName(), $annotation
                            );
                            break;
                        case $annotation instanceof Id:
                            $this->registerId(
                                $metaDataDefinition, $class, $rProperty->getName(), $annotation
                            );
                            break;
                        case $annotation instanceof Type:
                            $this->registerType(
                                $metaDataDefinition, $class, $rProperty->getName(), $annotation
                            );
                            break;
                    }
                }
            }
        }
    }
    /**
     * Process the embeddedReference annotations.
     *
     * @param Definition $metaDataDefinition
     * @param $class
     * @param $property
     * @param EmbeddedReference $annotation
     *
     * @return $this
     */
    protected function registerEmbeddedReference(
        Definition $metaDataDefinition, $class, $property, EmbeddedReference $annotation
    )
    {
        $metaDataDefinition->addMethodCall(
            'addClassPropertyEmbeddedReference',
            [$class, $property, get_object_vars($annotation)]
        );

        return $this;
    }
    /**
     * Process the embeddedReferenceList annotations.
     *
     * @param Definition $metaDataDefinition
     * @param $class
     * @param $property
     * @param EmbeddedReferenceList $annotation
     *
     * @return $this
     */
    protected function registerEmbeddedReferenceList(
        Definition $metaDataDefinition, $class, $property, EmbeddedReferenceList $annotation
    )
    {
        $metaDataDefinition->addMethodCall(
            'addClassPropertyEmbeddedReferenceList',
            [$class, $property, get_object_vars($annotation)]
        );

        return $this;
    }
    /**
     * Process the refresh annotations.
     *
     * @param Definition $metaDataDefinition
     * @param $class
     * @param $property
     * @param Refresh $annotation
     *
     * @return $this
     */
    protected function registerRefresh(
        Definition $metaDataDefinition, $class, $property, Refresh $annotation
    )
    {
        $metaDataDefinition->addMethodCall(
            'addClassPropertyRefresh',
            [$class, $property, get_object_vars($annotation)]
        );

        return $this;
    }
    /**
     * Process the generated annotations.
     *
     * @param Definition $metaDataDefinition
     * @param $class
     * @param $property
     * @param Generated $annotation
     *
     * @return $this
     */
    protected function registerGenerated(
        Definition $metaDataDefinition, $class, $property, Generated $annotation
    )
    {
        $metaDataDefinition->addMethodCall(
            'addClassPropertyGenerated',
            [$class, $property, get_object_vars($annotation)]
        );

        return $this;
    }
    /**
     * Process the id annotations.
     *
     * @param Definition $metaDataDefinition
     * @param $class
     * @param $property
     * @param Id $annotation
     *
     * @return $this
     */
    protected function registerId(
        Definition $metaDataDefinition, $class, $property, Id $annotation
    )
    {
        $metaDataDefinition->addMethodCall(
            'addClassPropertyId',
            [$class, $property, get_object_vars($annotation)]
        );

        return $this;
    }
    /**
     * Process the type annotations.
     *
     * @param Definition $metaDataDefinition
     * @param $class
     * @param $property
     * @param Type $annotation
     *
     * @return $this
     */
    protected function registerType(
        Definition $metaDataDefinition, $class, $property, Type $annotation
    )
    {
        $metaDataDefinition->addMethodCall(
            'setClassPropertyType',
            [$class, $property, get_object_vars($annotation)]
        );

        return $this;
    }
}