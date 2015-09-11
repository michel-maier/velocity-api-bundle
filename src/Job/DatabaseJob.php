<?php

/*
 * This file is part of the VELOCITY package.
 *
 * (c) PHPPRO <opensource@phppro.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Velocity\Bundle\ApiBundle\Job;

use Velocity\Bundle\ApiBundle\Traits\ServiceAware;
use Velocity\Bundle\ApiBundle\Job\Base\AbstractJob;
use Velocity\Bundle\ApiBundle\Annotation as Velocity;
use Velocity\Bundle\ApiBundle\Service\DatabaseService;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * DatabaseJobs Action.
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
class DatabaseJob extends AbstractJob
{
    use ServiceAware\DatabaseServiceAwareTrait;
    /**
     * @param DatabaseService          $databaseService
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(DatabaseService $databaseService, EventDispatcherInterface $eventDispatcher)
    {
        $this->setDatabaseService($databaseService);
        $this->setEventDispatcher($eventDispatcher);
    }
    /**
     * @Velocity\Job("db_stats")
     */
    public function stats()
    {
        $this->dispatch('db.stats.retrieved', $this->getDatabaseService()->getStatistics());
    }
}
