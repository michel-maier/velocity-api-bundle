<?php

namespace Velocity\Bundle\ApiBundle\Traits;

use Velocity\Bundle\ApiBundle\Service\DatabaseService;

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