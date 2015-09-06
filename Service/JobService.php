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
use Velocity\Bundle\ApiBundle\Traits\ServiceTrait;

/**
 * Job Service.
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
class JobService
{
    use ServiceTrait;
    /**
     * Return the list of registered jobs.
     *
     * @return callable[]
     */
    public function find()
    {
        return $this->getArrayParameter('jobs');
    }
    /**
     * Register a job by its name.
     *
     * @param string   $id
     * @param callable $callable
     * @param array    $options
     *
     * @throws \Exception
     *
     * @return $this
     */
    public function add($id, $callable, $options = [])
    {
        if (!is_callable($callable)) {
            throw $this->createUnexpectedException("Registered job must be a callable for '%s'", $id);
        }

        return $this->setArrayParameterKey('jobs', $id, ['callable' => $callable, 'options' => $options]);
    }
    /**
     * Return the job registered for the specified name.
     *
     * @param string $id
     *
     * @return mixed
     *
     * @throws \Exception
     */
    public function get($id)
    {
        return $this->getArrayParameterKey('jobs', $id);
    }
    /**
     * @param string $id
     * @param array  $params
     * @param array  $options
     *
     * @return mixed
     */
    public function execute($id, array $params = [], array $options = [])
    {
        $job = $this->get($id);

        return call_user_func_array($job['callable'], [$params, ['job' => $id] + $options + $job['options']]);
    }
}
