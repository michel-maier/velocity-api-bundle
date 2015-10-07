<?php
namespace Velocity\Bundle\ApiBundle\Tests\Mock;

use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Mock for easy velocity definition creation.
 *
 * @author Gabriele Santini <gab.santini@gmail.com>
 */
class ContainerBuilderMock extends ContainerBuilder
{
    /**
     * (non-PHPdoc)
     * @see \Symfony\Component\DependencyInjection\ContainerBuilder::getDefinition()
     *
     * @param string $id
     */
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
