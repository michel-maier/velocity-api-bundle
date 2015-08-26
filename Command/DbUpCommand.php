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

use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Velocity\Bundle\ApiBundle\Command\Base\ApiCommand;
use Velocity\Bundle\ApiBundle\Traits\MigrationServiceAwareTrait;

/**
 * DB Up Commmand
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
class DbUpCommand extends ApiCommand
{
    use MigrationServiceAwareTrait;
    /**
     * Configure the command.
     *
     * @return void
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
     * Execute the command.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->getMigrationService()->upgrade();
    }
}
