<?php

/*
 * This file is part of the VELOCITY package.
 *
 * (c) PHPPRO <opensource@phppro.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Velocity\Bundle\ApiBundle\Traits\ServiceAware;

use Velocity\Bundle\ApiBundle\Service\DatabaseService;

/**
 * DatabaseServiceAware trait.
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
trait DatabaseServiceAwareTrait
{
    /**
     * @param string $key
     * @param mixed  $service
     *
     * @return $this
     */
    protected abstract function setService($key, $service);
    /**
     * @param string $key
     *
     * @return mixed
     */
    protected abstract function getService($key);
    /**
     * @param DatabaseService $service
     *
     * @return $this
     */
    public function setDatabaseService(DatabaseService $service)
    {
        return $this->setService('database', $service);
    }
    /**
     * @return DatabaseService
     */
    public function getDatabaseService()
    {
        return $this->getService('database');
    }
}
