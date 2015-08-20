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

use Velocity\Bundle\ApiBundle\Traits\ServiceAware;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Velocity\Bundle\ApiBundle\Command\Base\ApiCommand;

/**
 * DB Drop Commmand
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
class DbDropCommand extends ApiCommand
{
    use ServiceAware\DatabaseServiceAwareTrait;
    /**
     * Configure the command
     *
     * @return void
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
     * Execute the command
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->getDatabaseService()->drop();
    }
}