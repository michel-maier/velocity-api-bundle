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

use Velocity\Bundle\ApiBundle\Service\MangoPayService;
use Velocity\Core\Traits\ServiceTrait;
use Velocity\Bundle\ApiBundle\Traits\ServiceAware;
use Velocity\Bundle\ApiBundle\PaymentProviderInterface;

/**
 * MangoPay Payment Provider.
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
class MangoPayPaymentProvider implements PaymentProviderInterface
{
    use ServiceTrait;
    use ServiceAware\MangoPayServiceAwareTrait;
    /**
     * MangoPayPaymentProvider constructor.
     *
     * @param MangoPayService $mangoPayService
     */
    public function __construct(MangoPayService $mangoPayService)
    {
        $this->setMangoPayService($mangoPayService);
    }

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
        throw $this->createNotYetImplementedException('MangoPay payment provider not yet implemented');
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
        throw $this->createNotYetImplementedException('MangoPay payment provider not yet implemented');
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
        throw $this->createNotYetImplementedException('MangoPay payment provider not yet implemented');
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
        throw $this->createNotYetImplementedException('MangoPay payment provider not yet implemented');
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
        throw $this->createNotYetImplementedException('MangoPay payment provider not yet implemented');
    }
}
