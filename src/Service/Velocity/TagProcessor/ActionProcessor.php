<?php
namespace Velocity\Bundle\ApiBundle\Service\Velocity\TagProcessor;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Velocity\Bundle\ApiBundle\Annotation as Velocity;

/**
 * Action Tag Processor.
 *
 * @author Gabriele Santini <gab.santini@gmail.com>
 */
class ActionProcessor extends AbstractTagProcessor
{
    /**
     * Process action tags.
     *
     * @param ContainerBuilder $container
     *
     * @throws \Exception
     */
    public function process(ContainerBuilder $container)
    {
        $actionDefinition = $container->getDefinition($this->getDefault('action.key'));
        foreach ($this->findVelocityTaggedServiceIds($container, 'action') as $id => $attributes) {
            $d = $container->getDefinition($id);
            foreach ($attributes as $params) {
                unset($params);
                $rClass = new \ReflectionClass($d->getClass());
                foreach ($rClass->getMethods(\ReflectionProperty::IS_PUBLIC) as $rMethod) {
                    foreach ($this->getAnnotationReader()->getMethodAnnotations($rMethod) as $a) {
                        $vars   = get_object_vars($a);
                        $method = $rMethod->getName();
                        switch (true) {
                            case $a instanceof Velocity\Action:
                                $name = $vars['value'];
                                unset($vars['value']);
                                $actionDefinition->addMethodCall('register', [$name, [$this->ref($id), $method], $vars]);
                                break;
                        }
                    }
                }
            }
        }
    }
}
