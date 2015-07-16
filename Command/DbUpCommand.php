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
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Velocity\Bundle\ApiBundle\Traits\ServiceTrait;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Velocity\Bundle\ApiBundle\Service\MigrationService;

/**
 * DB Up Commmand
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
class DbUpCommand extends Command
{
    use ServiceTrait;
    /**
     * @param MigrationService $migrationService
     */
    public function __construct(MigrationService $migrationService)
    {
        parent::__construct();
        $this->setMigrationService($migrationService);
    }
    /**
     * @param MigrationService $migrationService
     *
     * @return $this
     */
    public function setMigrationService(MigrationService $migrationService)
    {
        return $this->setService('migration', $migrationService);
    }
    /**
     * @return MigrationService
     */
    public function getMigrationService()
    {
        return $this->getService('migration');
    }
    /**
     *
     */
    protected function configure()
    {
        $this
            ->setName('api:db:up')
            ->setAliases(['up'])
            ->setDescription('Upgrade database')
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
        $this->getMigrationService()->upgrade();
    }
}