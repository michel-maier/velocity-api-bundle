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
 * Business Rule List Commmand
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
class BusinessRuleListCommand extends ApiCommand
{
    use ServiceAware\BusinessRuleServiceAwareTrait;
    /**
     * Configure the command.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('velocity:business-rule:list')
            ->setDescription('Liste business rules')
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
        foreach ($this->getBusinessRuleService()->getBusinessRules()['models'] as $model => $operations) {
            foreach ($operations as $operation => $businessRules) {
                foreach ($businessRules as $businessRule) {
                    $output->writeln(sprintf(" <info>%s</info> on %s %s <comment>%s</comment>", $businessRule['id'], str_replace('.', ' ', $model), $operation, $businessRule['name']));
                }
            }
        }
    }
}
