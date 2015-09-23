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
 * Formatter Service.
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
class FormatterService
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
     * Register a formatter for the type (replace if exist).
     *
     * @param string   $type
     * @param callable $callable
     * @param array    $options
     *
     * @return $this
     *
     * @throws \Exception
     */
    public function register($type, $callable, array $options = [])
    {
        $this->getCallableService()->registerByType('formatter', $type, $callable, $options);

        return $this;
    }
    /**
     * @param string $type
     * @param mixed  $data
     * @param array  $options
     *
     * @return mixed
     *
     * @throws \Exception
     */
    public function format($type, $data = null, array $options = [])
    {
        return $this->getCallableService()->executeByType('formatter', $type, [$data, ['format' => $type] + $options]);
    }
    /**
     * Return the formatter registered for the specified content type.
     *
     * @param string $type
     *
     * @return bool
     */
    public function has($type)
    {
        return $this->getCallableService()->hasByType('formatter', $type);
    }
}
