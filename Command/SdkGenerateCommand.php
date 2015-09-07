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
use Velocity\Bundle\ApiBundle\Traits\ServiceAware;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Velocity\Bundle\ApiBundle\Command\Base\ApiCommand;

/**
 * Generate Sdk Commmand
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
class SdkGenerateCommand extends ApiCommand
{
    use ServiceAware\SdkServiceAwareTrait;
    /**
     * Configure the command.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('velocity:sdk:generate')
            ->setDescription('Generate SDK source code')
            ->addArgument('target', InputArgument::REQUIRED, 'target directory')
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
        $this->getSdkService()->generate($input->getArgument('target'));
    }
}
