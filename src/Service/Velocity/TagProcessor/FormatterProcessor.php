<?php
namespace Velocity\Bundle\ApiBundle\Service\Velocity\TagProcessor;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Velocity\Bundle\ApiBundle\Annotation as Velocity;

/**
 * Formatter Processor.
 *
 * @author Gabriele Santini <gab.santini@gmail.com>
 */
class FormatterProcessor extends AbstractTagProcessor
{
    /**
     * Process formatter tags.
     *
     * @param ContainerBuilder $container
     *
     * @throws \Exception
     */
    public function process(ContainerBuilder $container)
    {
        $formatterDefinition = $container->getDefinition($this->getDefault('formatter.key'));

        foreach ($this->findVelocityTaggedServiceIds($container, 'formatter') as $id => $attributes) {
            $d = $container->getDefinition($id);
            foreach ($attributes as $params) {
                unset($params);
                $rClass = new \ReflectionClass($d->getClass());
                foreach ($rClass->getMethods(\ReflectionProperty::IS_PUBLIC) as $rMethod) {
                    foreach ($this->getAnnotationReader()->getMethodAnnotations($rMethod) as $a) {
                        $vars   = get_object_vars($a);
                        $method = $rMethod->getName();
                        switch (true) {
                            case $a instanceof Velocity\Formatter:
                                $type = $vars['value'];
                                unset($vars['value']);
                                $formatterDefinition->addMethodCall('register', [$type, [$this->ref($id), $method], $vars]);
                                break;
                        }
                    }
                }
            }
        }
    }
}
