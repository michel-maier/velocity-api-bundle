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
 * Workflow Interface.
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
interface WorkflowInterface
{
    /**
     * @param string $currentStep
     * @param string $targetStep
     *
     * @return bool
     */
    public function hasTransition($currentStep, $targetStep);
    /**
     * @param string $transition
     *
     * @return array
     */
    public function getTransitionAliases($transition);
}
