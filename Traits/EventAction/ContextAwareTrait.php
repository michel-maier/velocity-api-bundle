<?php

/*
 * This file is part of the VELOCITY package.
 *
 * (c) PHPPRO <opensource@phppro.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Velocity\Bundle\ApiBundle\Traits\EventAction;

use Velocity\Bundle\ApiBundle\EventAction\Context;

/**
 * ContextAware trait.
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
trait ContextAwareTrait
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
     * @return Context
     */
    public function getContext()
    {
        return $this->getService('eventActionContext');
    }
    /**
     * @param Context $context
     *
     * @return $this
     */
    public function setContext(Context $context)
    {
        return $this->setService('eventActionContext', $context);
    }
}
