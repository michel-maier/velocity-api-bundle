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
class EventActionListCommand extends ApiCommand
{
    use ServiceAware\EventActionServiceAwareTrait;
    /**
     * Configure the command.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('velocity:event-action:list')
            ->setDescription('List event actions')
            ->addOption('action', null, InputOption::VALUE_REQUIRED, 'action filter')
            ->addOption('event', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'event(s) filter')
            ->addOption('details', null, InputOption::VALUE_NONE, 'display details')
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
        $action   = $input->hasOption('action') ? $input->getOption('action') : null;
        $events   = $input->getOption('event');
        $detailed = $input->getOption('details');
        $hasEventFilters = 0 < count($events);

        foreach ($this->getEventActionService()->getEventActionSequences() as $eventName => $sequence) {
            if ($hasEventFilters && !in_array($eventName, $events)) {
                continue;
            }
            if (!count($sequence)) {
                continue;
            }
            $output->writeln(sprintf(' on <info>%s</info>', str_replace(['.', '-'], ' ', $eventName)));
            foreach ($sequence as $eventAction) {
                if (null !== $action && $action !== $eventAction['name']) {
                    continue;
                }
                $details = '';
                $conditional = '';
                if (true === $detailed) {
                    $params = [] !== $eventAction['params'] ? $eventAction['params'] : [];
                    if (isset($params['if'])) {
                        $conditional .= ($conditional ? ' and ' : '').$params['if'];
                        unset($params['if']);
                    }
                    if (isset($params['ifNot'])) {
                        $conditional .= ($conditional ? ' and not ' : 'not ').$params['ifNot'];
                        unset($params['ifNot']);
                    }
                    if ($conditional) {
                        $conditional = 'if '.$conditional.' ';
                    }
                    foreach($params as $k => $v) {
                        $details .= ($details ? ' and ' : '').$k.' '.(is_array($v) ? json_encode($v) : '<comment>'.$v.'</comment>');
                    }
                    if ($details) {
                        $details = ' with '.$details;
                    }
                }

                $eventActionType = $this->getEventActionService()->getEventActionByName($eventAction['name']);

                $output->writeln(
                    sprintf(
                        '   - %s<comment>%s</comment>%s',
                        $conditional,
                        isset($eventActionType['options']['description']) ? $eventActionType['options']['description'] : str_replace(['.', '_'], ' ', $eventAction['name']),
                        $details
                    )
                );
            }
        }
    }
}
