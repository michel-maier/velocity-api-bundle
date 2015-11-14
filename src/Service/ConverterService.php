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
 * Converter Service.
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
class ConverterService
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
     * Register an converter for the specified name (replace if exist).
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
        $this->getCallableService()->registerByType('converter', $name, $callable, $options);

        return $this;
    }
    /**
     * Return the converter registered for the specified name.
     *
     * @param string $name
     *
     * @return callable
     *
     * @throws \Exception if no converter registered for this name
     */
    public function get($name)
    {
        return $this->getCallableService()->getByType('converter', $name);
    }
    /**
     * @param string $name
     * @param mixed  $data
     * @param array  $options
     *
     * @return mixed
     *
     * @throws \Exception
     */
    public function convert($name, $data = null, array $options = [])
    {
        return $this->getCallableService()->executeByType('converter', $name, [$data, $options]);
    }
}
