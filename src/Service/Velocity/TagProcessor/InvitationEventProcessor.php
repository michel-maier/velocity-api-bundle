<?php
namespace Velocity\Bundle\ApiBundle\Service\Velocity\TagProcessor;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Velocity\Bundle\ApiBundle\Annotation as Velocity;

class InvitationEventProcessor extends AbstractTagProcessor
{
    /**
     * Process invitation event tags.
     *
     * @param ContainerBuilder $container
     *
     * @throws \Exception
     */
    public function process(ContainerBuilder $container)
    {
            $invitationEventDefinition = $container->getDefinition($this->getDefault('invitationEvent.key'));

        foreach ($this->findVelocityTaggedServiceIds($container, 'invitation_event') as $id => $attributes) {
            $d = $container->getDefinition($id);
            foreach ($attributes as $params) {
                unset($params);
                $rClass = new \ReflectionClass($d->getClass());
                foreach ($rClass->getMethods(\ReflectionProperty::IS_PUBLIC) as $rMethod) {
                    foreach ($this->getAnnotationReader()->getMethodAnnotations($rMethod) as $a) {
                        $vars   = get_object_vars($a);
                        $method = $rMethod->getName();
                        switch (true) {
                            case $a instanceof Velocity\InvitationEvent:
                                if (!isset($vars['type'])) {
                                    $vars['type'] = isset($vars['value']) ? $vars['value'] : null;
                                }
                                $ieType = $vars['type'];
                                $ieTransition = $vars['transition'];
                                unset($vars['value'], $vars['type'], $vars['transition']);
                                $invitationEventDefinition->addMethodCall('register', [$ieType, $ieTransition, [$this->ref($id), $method], $vars]);
                                break;
                        }
                    }
                }
            }
        }
    }
}
