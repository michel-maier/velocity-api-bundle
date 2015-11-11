<?php

/*
 * This file is part of the VELOCITY package.
 *
 * (c) PHPPRO <opensource@phppro.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Velocity\Bundle\ApiBundle\PaymentProvider;

use Velocity\Bundle\ApiBundle\PaymentProviderInterface;

/**
 * Fake Payment Provider.
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
class FakePaymentProvider implements PaymentProviderInterface
{
    /**
     * @param array $data
     * @param array $options
     *
     * @return array
     *
     * @throws \Exception
     */
    public function create($data = [], $options = [])
    {
        return ['url' => 'http://www.google.fr'];
    }
    /**
     * @param string|array $id
     * @param array        $data
     * @param array        $options
     *
     * @return array
     *
     * @throws \Exception
     */
    public function confirm($id, $data = [], $options = [])
    {
        return [];
    }
    /**
     * @param string|array $id
     * @param array        $data
     * @param array        $options
     *
     * @return array
     *
     * @throws \Exception
     */
    public function fail($id, $data = [], $options = [])
    {
        return [];
    }
    /**
     * @param string|array $id
     * @param array        $data
     * @param array        $options
     *
     * @return array
     *
     * @throws \Exception
     */
    public function cancel($id, $data = [], $options = [])
    {
        return [];
    }
    /**
     * @param string|array $id
     * @param array        $options
     *
     * @return array
     *
     * @throws \Exception
     */
    public function get($id, $options = [])
    {
        return [];
    }
}
