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

use Velocity\Bundle\ApiBundle\Bag;
use Velocity\Bundle\ApiBundle\Traits\ServiceAware;
use Velocity\Bundle\ApiBundle\Service\BatchService;
use Velocity\Bundle\ApiBundle\Annotation as Velocity;
use Velocity\Bundle\ApiBundle\Action\Base\AbstractAction;

/**
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
class BatchAction extends AbstractAction
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
     * @param Bag $params
     * @param Bag $context
     *
     * @Velocity\Action("batch", description="execute a batch")
     */
    public function executeBatch(Bag $params, Bag $context)
    {
        $results = $this->getBatchService()->execute($params->get('name'), $params->get('params', []));

        if (is_array($results)) {
            $context->set($results);
        }
    }
}
