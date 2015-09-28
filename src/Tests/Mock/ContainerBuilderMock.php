<?php
namespace Velocity\Bundle\ApiBundle\Tests\Mock;

use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ContainerBuilderMock extends ContainerBuilder
{
    public function getDefinition($id)
    {
        $id = strtolower($id);
        if (!$this->hasDefinition($id) && 'velocity' === strstr($id, '.', true)) {
            $this->setDefinition($id, new Definition());
        } else {
            parent::getDefinition($id);
        }
    }
}