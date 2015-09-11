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
 * Client Provider Interface.
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
interface ClientProviderInterface
{
    /**
     * Return the specified client.
     *
     * @param string $id
     * @param array  $fields
     * @param array  $options
     *
     * @return mixed
     */
    public function get($id, $fields = [], $options = []);
}
