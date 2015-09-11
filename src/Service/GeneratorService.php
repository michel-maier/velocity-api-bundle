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
 * Generator Service.
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
class GeneratorService
{
    use ServiceTrait;
    /**
     * List of generators.
     *
     * @var callable[]
     */
    protected $generators = [];
    /**
     * Return the list of registered generators.
     *
     * @return callable[]
     */
    public function getGenerators()
    {
        return $this->generators;
    }
    /**
     * Register a generator for the specified name (replace if exist).
     *
     * @param string   $name
     * @param callable $callable
     * @param array    $options
     *
     * @throws \Exception
     *
     * @return $this
     */
    public function register($name, $callable, $options = [])
    {
        if (!is_callable($callable)) {
            throw $this->createUnexpectedException("Registered generator must be a callable for '%s'", $name);
        }

        $this->generators[$name] = ['callable' => $callable, 'options' => $options];

        return $this;
    }
    /**
     * @param string $name
     *
     * @return $this
     *
     * @throws \Exception
     */
    public function checkGeneratorExist($name)
    {
        if (!isset($this->generators[$name])) {
            throw $this->createRequiredException(
                "No generator registered for '%s'",
                $name
            );
        }

        return $this;
    }
    /**
     * Return the generator registered for the specified name.
     *
     * @param string $name
     *
     * @return callable
     *
     * @throws Exception if no generator registered for this name
     */
    public function getGeneratorByName($name)
    {
        $this->checkGeneratorExist($name);

        return $this->generators[$name];
    }
    /**
     * @param string $name
     * @param array  $params
     * @param array  $options
     *
     * @return mixed
     */
    public function generate($name, array $params = [], array $options = [])
    {
        $generator = $this->getGeneratorByName($name);

        return call_user_func_array($generator['callable'], [$params, $options + $generator['options']]);
    }
}
