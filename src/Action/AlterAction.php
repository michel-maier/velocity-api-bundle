<?php

/*
 * This file is part of the VELOCITY package.
 *
 * (c) PHPPRO <opensource@phppro.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Velocity\Bundle\ApiBundle\Action;

use Velocity\Core\Bag;
use Velocity\Bundle\ApiBundle\RepositoryInterface;
use Velocity\Bundle\ApiBundle\Annotation as Velocity;
use Velocity\Bundle\ApiBundle\Action\Base\AbstractAction;

/**
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
class AlterAction extends AbstractAction
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
     * @param Bag $params
     *
     * @Velocity\Action("alter", description="update database")
     */
    public function alter(Bag $params)
    {
        $repo    = strtolower($params->get('repo'));
        $id      = $params->get('id');
        $set     = $params->get('set', []);
        $inc     = $params->get('inc', []);
        $mul     = $params->get('mul', []);
        $min     = $params->get('min', []);
        $max     = $params->get('max', []);
        $unset   = $params->get('unset', []);

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
