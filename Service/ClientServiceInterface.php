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

/**
 * Client Service Interface.
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
interface ClientServiceInterface
{
    /**
     * Return the specified client.
     *
     * @param string $id
     * @param array  $fields
     * @param array  $options
     *
     * @return array
     */
    public function get($id, $fields = [], $options = []);
}