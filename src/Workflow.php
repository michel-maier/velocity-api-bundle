<?php

/*
 * This file is part of the VELOCITY package.
 *
 * (c) PHPPRO <opensource@phppro.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Velocity\Bundle\ApiBundle;

/**
 * Workflow.
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
class Workflow implements WorkflowInterface
{
    /**
     * @var array
     */
    protected $steps;
    /**
     * @var array
     */
    protected $transitions;
    /**
     * @param array $definition
     */
    public function __construct(array $definition = [])
    {
        $definition += ['steps' => [], 'transitions' => []];

        if (!is_array($definition['steps'])) {
            $definition['steps'] = [];
        }

        if (!is_array($definition['transitions'])) {
            $definition['transitions'] = [];
        }

        foreach ($definition['steps'] as $stepName) {
            $this->addStep($stepName);
        }

        foreach ($definition['transitions'] as $stepName => $transitions) {
            if (!is_array($transitions)) {
                $transitions = [];
            }
            foreach ($transitions as $targetStep) {
                $this->addTransition($stepName, $targetStep);
            }
        }
    }
    /**
     * @param string $currentStep
     * @param string $targetStep
     *
     * @return bool
     */
    public function hasTransition($currentStep, $targetStep)
    {
        return isset($this->transitions[$currentStep][$targetStep]);
    }
    /**
     * @param string $name
     *
     * @return $this
     */
    public function addStep($name)
    {
        $this->steps[$name] = [];

        return $this;
    }
    /**
     * @param string $from
     * @param string $to
     *
     * @return $this
     */
    public function addTransition($from, $to)
    {
        $this->checkStepExist($from);
        $this->checkStepExist($to);

        if (!isset($this->transitions[$from])) {
            $this->transitions[$from] = [];
        }

        $this->transitions[$from][$to] = [];

        return $this;
    }
    /**
     * @param string $step
     *
     * @return $this
     */
    public function checkStepExist($step)
    {
        if (!$this->hasStep($step)) {
            throw new \RuntimeException(sprintf("Unknown step '%s'", $step), 500);
        }

        return $this;
    }
    /**
     * @param string $step
     *
     * @return bool
     */
    public function hasStep($step)
    {
        return isset($this->steps[$step]);
    }
}
