<?php
namespace Velocity\Bundle\ApiBundle\Service\Velocity\TagProcessor;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Velocity\Bundle\ApiBundle\Annotation as Velocity;

/**
 * Code Generator Processor.
 *
 * @author Gabriele Santini <gab.santini@gmail.com>
 */
class CodeGeneratorProcessor extends AbstractTagProcessor
{
    /**
     * Process code generator tags.
     *
     * @param ContainerBuilder $container
     *
     * @throws \Exception
     */
    public function process(ContainerBuilder $container)
    {
        $codeGeneratorDefinition = $container->getDefinition($this->getDefault('codeGenerator.key'));

        foreach ($this->findVelocityTaggedServiceIds($container, 'codeGenerator') as $id => $attributes) {
            $d = $container->getDefinition($id);
            foreach ($attributes as $params) {
                unset($params);
                $rClass = new \ReflectionClass($d->getClass());
                foreach ($rClass->getMethods(\ReflectionProperty::IS_PUBLIC) as $rMethod) {
                    foreach ($this->getAnnotationReader()->getMethodAnnotations($rMethod) as $a) {
                        $vars   = get_object_vars($a);
                        $method = $rMethod->getName();
                        switch (true) {
                            case $a instanceof Velocity\CodeGeneratorMethodType:
                                $name = $vars['value'];
                                unset($vars['value']);
                                $codeGeneratorDefinition->addMethodCall('registerMethodType', [$name, [$this->ref($id), $method], $vars]);
                                break;
                        }
                    }
                }
            }
        }
    }
}
