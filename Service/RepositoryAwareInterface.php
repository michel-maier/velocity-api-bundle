<?php

/*
 * This file is part of the VELOCITY package.
 *
 * (c) PHPPRO <opensource@phppro.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Velocity\Bundle\ApiBundle\Service;

use Velocity\Bundle\ApiBundle\Repository\RepositoryInterface;

interface RepositoryAwareInterface
{
    /**
     * Set the underlying repository.
     *
     * @param RepositoryInterface $repository
     *
     * @return $this
     */
    public function setRepository(RepositoryInterface $repository);
    /**
     * Return the underlying repository.
     *
     * @return RepositoryInterface
     */
    public function getRepository();
}