<?php

/*
 * This file is part of the VELOCITY package.
 *
 * (c) PHPPRO <opensource@phppro.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Velocity\Bundle\ApiBundle\Traits\Document;

use Velocity\Bundle\ApiBundle\Event;
use Velocity\Bundle\ApiBundle\Service\MetaDataService;
use Velocity\Bundle\ApiBundle\Service\BusinessRuleService;

/**
 * Helper trait.
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
trait HelperTrait
{
    /**
     * @return int
     */
    public function getExpectedTypeCount()
    {
        return 1;
    }
    /**
     * @return MetaDataService
     */
    public abstract function getMetaDataService();
    /**
     * @return BusinessRuleService
     */
    public abstract function getBusinessRuleService();
    /**
     * @return string
     */
    protected abstract function getModelName();
    /**
     * Build the full event name.
     *
     * @param string $event
     *
     * @return string
     */
    protected abstract function buildEventName($event);
    /**
     * @param string $event
     * @param null   $data
     *
     * @return $this
     */
    protected abstract function dispatch($event, $data = null);
    /**
     * Trigger the specified document event if listener are registered.
     *
     * @param string $event
     * @param mixed  $data
     *
     * @return $this
     */
    protected function event($event, $data = null)
    {
        return $this->dispatch($this->buildEventName($event), new Event\DocumentEvent($data));
    }
    /**
     * Execute the registered callback and return the updated subject.
     *
     * @param string $key
     * @param mixed  $subject
     * @param array  $options
     *
     * @return mixed
     */
    protected function callback($key, $subject = null, $options = [])
    {
        return $this->getMetaDataService()->callback($this->buildEventName($key), $subject, $options);
    }
    /**
     * @param string $operation
     * @param mixed  $model
     * @param array  $options
     *
     * @return $this
     */
    protected function checkBusinessRules($operation, $model, array $options = [])
    {
        $this->getBusinessRuleService()->executeBusinessRulesForModelOperation(
            $this->getModelName(),
            $operation,
            $model,
            $options
        );

        return $this;
    }
}
