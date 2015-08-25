<?php

/*
 * This file is part of the VELOCITY package.
 *
 * (c) PHPPRO <opensource@phppro.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Velocity\Bundle\ApiBundle\Traits;

use Symfony\Component\Templating\EngineInterface;

/**
 * TemplatingAware trait.
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
trait TemplatingAwareTrait
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
     * @param EngineInterface $templating
     *
     * @return $this
     */
    public function setTemplating(EngineInterface $templating)
    {
        return $this->setService('templating', $templating);
    }
    /**
     * @return EngineInterface
     */
    public function getTemplating()
    {
        return $this->getService('templating');
    }
}