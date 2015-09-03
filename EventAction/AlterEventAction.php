<?php

/*
 * This file is part of the VELOCITY package.
 *
 * (c) PHPPRO <opensource@phppro.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Velocity\Bundle\ApiBundle\EventAction;

use Velocity\Bundle\ApiBundle\RepositoryInterface;
use Velocity\Bundle\ApiBundle\Annotation as Velocity;
use Velocity\Bundle\ApiBundle\EventAction\Base\AbstractEventAction;

/**
 * @author Olivier Hoareau <olivier@tomboladirecte.com>
 */
class AlterEventAction extends AbstractEventAction
{
    /**
     * @param string              $name
     * @param RepositoryInterface $repository
     *
     * @return $this
     */
    public function addRepository($name, RepositoryInterface $repository)
    {
        return $this->setArrayParameterKey('repositories', $name, $repository);
    }
    /**
     * @param string $name
     *
     * @return RepositoryInterface
     *
     * @throws \Exception
     */
    public function getRepository($name)
    {
        return $this->getArrayParameterKey('repositories', $name);
    }
    /**
     * @return RepositoryInterface[]
     */
    public function getRepositories()
    {
        return $this->getArrayParameter('repositories');
    }
    /**
     * @Velocity\EventAction("alter")
     */
    public function alter()
    {
        $context = $this->getContext();
        $repo    = $context->getRequiredVariable('repo');
        $id      = $context->getRequiredVariable('id');
        $set     = $context->getVariable('set', []);
        $inc     = $context->getVariable('inc', []);
        $mul     = $context->getVariable('mul', []);
        $min     = $context->getVariable('min', []);
        $max     = $context->getVariable('max', []);
        $unset   = $context->getVariable('unset', []);

        $changes  = [];
        $changes += count($set) ? ['$set' => $set] : [];
        $changes += count($inc) ? ['$inc' => $inc] : [];
        $changes += count($inc) ? ['$mul' => $mul] : [];
        $changes += count($inc) ? ['$min' => $min] : [];
        $changes += count($inc) ? ['$max' => $max] : [];
        $changes += count($inc) ? ['$unset' => $unset] : [];

        if (!count($changes)) {
            return;
        }

        $this->getRepository($repo)->alter($id, $changes);
    }
}
