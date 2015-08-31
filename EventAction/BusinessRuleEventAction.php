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

use Velocity\Bundle\ApiBundle\Traits\ServiceTrait;
use Velocity\Bundle\ApiBundle\Traits\ServiceAware;
use Velocity\Bundle\ApiBundle\Annotation as Velocity;
use Velocity\Bundle\ApiBundle\Service\BusinessRuleService;

/**
 * @author Olivier Hoareau <olivier@tomboladirecte.com>
 */
class BusinessRuleEventAction
{
    use ServiceTrait;
    use ServiceAware\BusinessRuleServiceAwareTrait;
    /**
     * @param BusinessRuleService $businessRuleService
     */
    public function __construct(BusinessRuleService $businessRuleService)
    {
        $this->setBusinessRuleService($businessRuleService);
    }
    /**
     * @param Context $context
     *
     * @Velocity\EventAction("business_rule", defaults={})
     */
    public function execute(Context $context)
    {
        if ($context->hasVariable('id')) {
            $this->getBusinessRuleService()->executeBusinessruleById($context->getVariable('id'), [null, null, null]);
        } else {
            $this->getBusinessRuleService()->executeBusinessRulesForModelOperation(
                $context->getVariable('model'),
                $context->getVariable('operation'),
                null
            );
        }
    }
}
