<?php

/*
 * This file is part of the VELOCITY package.
 *
 * (c) PHPPRO <opensource@phppro.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Velocity\Bundle\ApiBundle\EventAction;

use Velocity\Bundle\ApiBundle\Traits\ServiceAware;
use Velocity\Bundle\ApiBundle\Annotation as Velocity;
use Velocity\Bundle\ApiBundle\Service\BusinessRuleService;
use Velocity\Bundle\ApiBundle\EventAction\Base\AbstractEventAction;

/**
 * @author Olivier Hoareau <olivier@tomboladirecte.com>
 */
class BusinessRuleEventAction extends AbstractEventAction
{
    use ServiceAware\BusinessRuleServiceAwareTrait;
    /**
     * @param BusinessRuleService $businessRuleService
     */
    public function __construct(BusinessRuleService $businessRuleService)
    {
        $this->setBusinessRuleService($businessRuleService);
    }
    /**
     * @Velocity\EventAction("business_rule")
     */
    public function execute()
    {
        if ($this->getContext()->hasVariable('id')) {
            $this->getBusinessRuleService()->executeBusinessruleById($this->getContext()->getRequiredVariable('id'), [null, null, null]);
        } else {
            $this->getBusinessRuleService()->executeBusinessRulesForModelOperation(
                $this->getContext()->getRequiredVariable('model'),
                $this->getContext()->getRequiredVariable('operation'),
                null
            );
        }
    }
}
