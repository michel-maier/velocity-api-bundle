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

/**
 * Migrator Interface.
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
interface MigratorInterface
{
    /**
     * Process the upgrade path.
     *
     * @param string $path
     * @param array  $options
     *
     * @return $this
     *
     * @throws Exception
     */
    public function upgrade($path, $options = []);
}