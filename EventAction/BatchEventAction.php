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

use Velocity\Bundle\ApiBundle\Service\BatchService;
use Velocity\Bundle\ApiBundle\Traits\ServiceAware;
use Velocity\Bundle\ApiBundle\Annotation as Velocity;
use Velocity\Bundle\ApiBundle\EventAction\Base\AbstractEventAction;

/**
 * @author Olivier Hoareau <olivier@tomboladirecte.com>
 */
class BatchEventAction extends AbstractEventAction
{
    use ServiceAware\BatchServiceAwareTrait;
    /**
     * @param BatchService $batchService
     */
    public function __construct(BatchService $batchService)
    {
        $this->setBatchService($batchService);
    }
    /**
     * @Velocity\EventAction("batch", description="execute a batch")
     */
    public function executeBatch()
    {
        $context = $this->getContext();

        $results = $this->getBatchService()->execute(
            $context->getRequiredVariable('name'),
            $context->getVariable('params', [])
        );

        if (is_array($results)) {
            $context->setVariables($results);
        }
    }
}
