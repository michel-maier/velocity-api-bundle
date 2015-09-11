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

use Exception;
use Velocity\Bundle\ApiBundle\Traits\ServiceAware;
use Velocity\Bundle\ApiBundle\Traits\ServiceTrait;

/**
 * Archive Service.
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
class ArchiverService
{
    use ServiceTrait;
    /**
     * List of archivers.
     *
     * @var callable[]
     */
    protected $archivers = [];
    /**
     * Return the list of registered archivers.
     *
     * @return callable[]
     */
    public function getArchivers()
    {
        return $this->archivers;
    }
    /**
     * Register an archiver for the specified type (replace if exist).
     *
     * @param string   $type
     * @param callable $callable
     * @param array    $options
     *
     * @throws \Exception
     *
     * @return $this
     */
    public function register($type, $callable, $options = [])
    {
        if (!is_callable($callable)) {
            throw $this->createUnexpectedException("Registered archiver must be a callable for '%s'", $type);
        }

        $this->archivers[$type] = ['callable' => $callable, 'options' => $options];

        return $this;
    }
    /**
     * @param string $type
     *
     * @return $this
     *
     * @throws \Exception
     */
    public function checkArchiverExist($type)
    {
        if (!isset($this->archivers[$type])) {
            throw $this->createRequiredException(
                "No archiver registered for '%s'",
                $type
            );
        }

        return $this;
    }
    /**
     * Return the archiver registered for the specified type.
     *
     * @param string $type
     *
     * @return callable
     *
     * @throws Exception if no archiver registered for this type
     */
    public function getArchiverByType($type)
    {
        $this->checkArchiverExist($type);

        return $this->archivers[$type];
    }
    /**
     * @param string $type
     * @param mixed  $data
     * @param array  $options
     *
     * @return mixed
     */
    public function archive($type, $data, array $options = [])
    {
        $archiver = $this->getArchiverByType($type);

        return call_user_func_array($archiver['callable'], [$data, ['type' => $type] + $options + $archiver['options']]);
    }
}
