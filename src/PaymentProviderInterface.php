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
 * Payment Provider Interface.
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
interface PaymentProviderInterface
{
    /**
     * @param array $data
     * @param array $options
     *
     * @return array
     */
    public function create($data = [], $options = []);
    /**
     * @param string|array $id
     * @param array        $data
     * @param array        $options
     *
     * @return array
     */
    public function confirm($id, $data = [], $options = []);
    /**
     * @param string|array $id
     * @param array        $data
     * @param array        $options
     *
     * @return array
     */
    public function fail($id, $data = [], $options = []);
    /**
     * @param string|array $id
     * @param array        $data
     * @param array        $options
     *
     * @return array
     */
    public function cancel($id, $data = [], $options = []);
    /**
     * @param string|array $id
     * @param array        $options
     *
     * @return array
     */
    public function get($id, $options = []);
}
