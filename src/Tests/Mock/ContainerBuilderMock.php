<?php

/*
 * This file is part of the VELOCITY package.
 *
 * (c) PHPPRO <opensource@phppro.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Velocity\Bundle\ApiBundle\Tests\Mock;

use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Mock for easy velocity definition creation.
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 * @author Gabriele Santini <gab.santini@gmail.com>
 */
class ContainerBuilderMock extends ContainerBuilder
{
    /**
     * @param string $id
     *
     * @return Definition
     *
     * @see \Symfony\Component\DependencyInjection\ContainerBuilder::getDefinition()
     */
    public function getDefinition($id)
    {
        $id = strtolower($id);
        if (!$this->hasDefinition($id) && 'velocity' === strstr($id, '.', true)) {
            $this->setDefinition($id, new Definition());
        }

        parent::getDefinition($id);
    }
}
