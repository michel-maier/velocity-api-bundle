<?php

/*
 * This file is part of the VELOCITY package.
 *
 * (c) PHPPRO <opensource@phppro.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Velocity\Bundle\ApiBundle\Service;

use Velocity\Bundle\ApiBundle\Traits\ServiceAware;
use Velocity\Core\Traits\ServiceTrait;

/**
 * Job Service.
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
class JobService
{
    use ServiceTrait;
    use ServiceAware\CallableServiceAwareTrait;
    /**
     * @param CallableService $callableService
     */
    public function __construct(CallableService $callableService)
    {
        $this->setCallableService($callableService);
    }
    /**
     * Register an job for the specified name (replace if exist).
     *
     * @param string   $name
     * @param callable $callable
     * @param array    $options
     *
     * @return $this
     *
     * @throws \Exception
     */
    public function register($name, $callable, array $options = [])
    {
        $this->getCallableService()->registerByType('job', $name, $callable, $options);

        return $this;
    }
    /**
     * Register an job set for the specified name (replace if exist).
     *
     * @param string $name
     * @param array  $jobs
     * @param array  $options
     *
     * @return $this
     *
     * @throws \Exception
     */
    public function registerSet($name, array $jobs, array $options = [])
    {
        $this->getCallableService()->registerSetByType('job', $name, $jobs, $options);

        return $this;
    }
    /**
     * Return the job registered for the specified name.
     *
     * @param string $name
     *
     * @return callable
     *
     * @throws \Exception if no job registered for this name
     */
    public function get($name)
    {
        return $this->getCallableService()->getByType('job', $name);
    }
    /**
     * @param string $name
     * @param array  $params
     * @param array  $options
     *
     * @return mixed
     *
     * @throws \Exception
     */
    public function execute($name, array $params = [], array $options = [])
    {
        return $this->getCallableService()->executeByType('job', $name, [$params, ['job' => $name] + $options]);
    }
}
