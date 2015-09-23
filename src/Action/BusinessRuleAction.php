<?php

/*
 * This file is part of the VELOCITY package.
 *
 * (c) PHPPRO <opensource@phppro.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Velocity\Bundle\ApiBundle\Action;

use Velocity\Core\Bag;
use Velocity\Bundle\ApiBundle\Traits\ServiceAware;
use Velocity\Bundle\ApiBundle\Annotation as Velocity;
use Velocity\Bundle\ApiBundle\Action\Base\AbstractAction;
use Velocity\Bundle\ApiBundle\Service\BusinessRuleService;

/**
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
class BusinessRuleAction extends AbstractAction
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
     * @param Bag $params
     *
     * @Velocity\Action("business_rule", description="check a business rule")
     */
    public function execute(Bag $params)
    {
        if ($params->has('id')) {
            $this->getBusinessRuleService()->executeBusinessruleById($params->get('id'), [null, null, null]);
        } else {
            $this->getBusinessRuleService()->executeBusinessRulesForModelOperation(
                $params->get('model'),
                $params->get('operation'),
                null
            );
        }
    }
}
