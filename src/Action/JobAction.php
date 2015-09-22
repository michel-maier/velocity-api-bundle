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
use Velocity\Bundle\ApiBundle\Service\JobService;
use Velocity\Bundle\ApiBundle\Traits\ServiceAware;
use Velocity\Bundle\ApiBundle\Annotation as Velocity;
use Velocity\Bundle\ApiBundle\Action\Base\AbstractAction;

/**
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
class JobAction extends AbstractAction
{
    use ServiceAware\JobServiceAwareTrait;
    /**
     * @param JobService $jobService
     */
    public function __construct(JobService $jobService)
    {
        $this->setJobService($jobService);
    }
    /**
     * @param Bag $params
     * @param Bag $context
     *
     * @Velocity\Action("job", description="execute a job")
     */
    public function executeJob(Bag $params, Bag $context)
    {
        $results = $this->getJobService()->execute($params->get('name'), $params->get('params', []));

        if (is_array($results)) {
            $context->set($results);
        }
    }
}
