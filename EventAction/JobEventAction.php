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

use Velocity\Bundle\ApiBundle\Service\JobService;
use Velocity\Bundle\ApiBundle\Traits\ServiceAware;
use Velocity\Bundle\ApiBundle\Annotation as Velocity;
use Velocity\Bundle\ApiBundle\EventAction\Base\AbstractEventAction;

/**
 * @author Olivier Hoareau <olivier@tomboladirecte.com>
 */
class JobEventAction extends AbstractEventAction
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
     * @Velocity\EventAction("job", description="execute a job")
     */
    public function executeJob()
    {
        $context = $this->getContext();

        $results = $this->getJobService()->execute(
            $context->getRequiredVariable('name'),
            $context->getVariable('params', [])
        );

        if (is_array($results)) {
            $context->setVariables($results);
        }
    }
}
