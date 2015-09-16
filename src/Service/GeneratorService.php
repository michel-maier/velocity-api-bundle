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
 * Generator Service.
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
class GeneratorService
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
     * Register an generator for the specified name (replace if exist).
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
        $this->getCallableService()->registerByType('generator', $name, $callable, $options);

        return $this;
    }
    /**
     * Return the generator registered for the specified name.
     *
     * @param string $name
     *
     * @return callable
     *
     * @throws \Exception if no generator registered for this name
     */
    public function get($name)
    {
        return $this->getCallableService()->getByType('generator', $name);
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
    public function generate($name, array $params = [], array $options = [])
    {
        return $this->getCallableService()->executeByType('generator', $name, [$params, $options]);
    }
}
