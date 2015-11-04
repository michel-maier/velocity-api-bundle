<?php
namespace Velocity\Bundle\ApiBundle\Service\Velocity\TagProcessor;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Velocity\Bundle\ApiBundle\Annotation as Velocity;

class BusinessRuleProcessor extends AbstractTagProcessor
{
    /**
     * Process business rule tags.
     *
     * @param ContainerBuilder $container
     *
     * @throws \Exception
     */
    public function process(ContainerBuilder $container)
    {
        $businessRuleDefinition = $container->getDefinition($this->getDefault('businessRule.key'));

        foreach ($this->findVelocityTaggedServiceIds($container, 'business_rule') as $id => $attributes) {
            $d = $container->getDefinition($id);
            foreach ($attributes as $params) {
                unset($params);
                $rClass = new \ReflectionClass($d->getClass());
                foreach ($rClass->getMethods(\ReflectionProperty::IS_PUBLIC) as $rMethod) {
                    foreach ($this->getAnnotationReader()->getMethodAnnotations($rMethod) as $a) {
                        $vars   = get_object_vars($a);
                        $method = $rMethod->getName();
                        switch (true) {
                            case $a instanceof Velocity\BusinessRule:
                                if (!isset($vars['id'])) {
                                    $vars['id'] = isset($vars['value']) ? $vars['value'] : null;
                                }
                                $brId = strtoupper($vars['id']);
                                $brName = strtolower(isset($vars['name']) ? $vars['name'] : trim(join(' ', preg_split('/(?=\\p{Lu})/', ucfirst($method)))));
                                unset($vars['value'], $vars['id'], $vars['name']);
                                $businessRuleDefinition->addMethodCall('register', [$brId, $brName, [$this->ref($id), $method], $vars]);
                                break;
                        }
                    }
                }
            }
        }
    }
}
