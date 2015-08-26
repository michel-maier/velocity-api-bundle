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

use Velocity\Bundle\ApiBundle\Service\GeneratorService;

/**
 * GeneratorServiceAware trait.
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
trait GeneratorServiceAwareTrait
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
     * @return GeneratorService
     */
    public function getGeneratorService()
    {
        return $this->getService('generator');
    }
    /**
     * @param GeneratorService $service
     *
     * @return $this
     */
    public function setGeneratorService(GeneratorService $service)
    {
        return $this->setService('generator', $service);
    }
}
