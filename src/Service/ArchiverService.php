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
 * Archiver Service.
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
class ArchiverService
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
     * Register an archiver for the specified name (replace if exist).
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
        $this->getCallableService()->registerByType('archiver', $type, $callable, $options);

        return $this;
    }
    /**
     * Return the archiver registered for the specified name.
     *
     * @param string $type
     *
     * @return callable
     *
     * @throws \Exception if no archiver registered for this name
     */
    public function get($type)
    {
        return $this->getCallableService()->getByType('archiver', $type);
    }
    /**
     * @param string $type
     * @param array  $data
     * @param array  $options
     *
     * @return mixed
     *
     * @throws \Exception
     */
    public function archive($type, $data, array $options = [])
    {
        return $this->getCallableService()->executeByType('archiver', $type, [$data, ['type' => $type] + $options]);
    }
}
