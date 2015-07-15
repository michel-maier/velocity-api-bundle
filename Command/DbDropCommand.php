<?php

/*
 * This file is part of the VELOCITY package.
 *
 * (c) PHPPRO <opensource@phppro.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Velocity\Bundle\ApiBundle\Command;

use Symfony\Component\Console\Command\Command;
use Velocity\Bundle\ApiBundle\Traits\ServiceTrait;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Velocity\Bundle\ApiBundle\Service\DatabaseService;
use Velocity\Bundle\ApiBundle\Traits\DatabaseServiceAwareTrait;

/**
 * DB Drop Commmand
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
class DbDropCommand extends Command
{
    use ServiceTrait;
    use DatabaseServiceAwareTrait;
    /**
     * @param DatabaseService $databaseService
     */
    public function __construct(DatabaseService $databaseService)
    {
        parent::__construct();
        $this->setDatabaseService($databaseService);
    }
    /**
     *
     */
    protected function configure()
    {
        $this
            ->setName('api:db:drop')
            ->setAliases(['drop'])
            ->setDescription('Drop database')
        ;
    }
    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->getDatabaseService()->drop();
    }
}