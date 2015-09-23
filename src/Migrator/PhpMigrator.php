<?php

/*
 * This file is part of the VELOCITY package.
 *
 * (c) PHPPRO <opensource@phppro.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Velocity\Bundle\ApiBundle\Migrator;

use Exception;
use Psr\Log\LoggerAwareInterface;
use Velocity\Bundle\ApiBundle\MigratorInterface;
use Velocity\Core\Traits\ServiceTrait;
use Velocity\Core\Traits\LoggerAwareTrait;
use Velocity\Core\Traits\ContainerAwareTrait;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

/**
 * PHP Migrator.
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
class PhpMigrator implements MigratorInterface, ContainerAwareInterface, LoggerAwareInterface
{
    use ServiceTrait;
    use LoggerAwareTrait;
    use ContainerAwareTrait;
    /**
     * Process the upgrade path.
     *
     * @param string $path
     * @param array  $options
     *
     * @return $this
     *
     * @throws \Exception
     */
    public function upgrade($path, $options = [])
    {
        $container  = $this->getContainer();
        $logger     = $this->getLogger();
        $dispatcher = $this->getEventDispatcher();

        if (!is_file($path)) {
            throw $this->createNotFoundException("Unknown PHP Diff file '%s'", $path);
        }

        include $path;

        unset($options);
        unset($container);
        unset($logger);
        unset($dispatcher);

        return $this;
    }
}
