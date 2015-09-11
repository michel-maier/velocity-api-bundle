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
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Batch Service.
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
class BatchService
{
    use ServiceTrait;
    /**
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->setEventDispatcher($eventDispatcher);
    }
    /**
     * @param string $name
     * @param array  $params
     * @param array  $options
     *
     * @return $this
     *
     * @throws \Exception
     */
    public function execute($name, array $params = [], array $options = [])
    {
        $eventName = 'batchs.'.$name;

        if (!$this->hasListeners($eventName)) {
            throw $this->createNotFoundException("Unknown batch '%s'", $name);
        }

        return $this->dispatch($eventName, $params + ['options' => $options]);
    }
}
