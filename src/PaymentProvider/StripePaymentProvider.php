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

use Symfony\Component\HttpFoundation\Request;
use Velocity\Core\Traits\ServiceTrait;
use Velocity\Bundle\ApiBundle\Traits\ServiceAware;
use Velocity\Bundle\ApiBundle\Service\StripeService;
use Velocity\Bundle\ApiBundle\PaymentProviderInterface;

/**
 * Stripe Payment Provider.
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
class StripePaymentProvider implements PaymentProviderInterface
{
    use ServiceTrait;
    use ServiceAware\StripeServiceAwareTrait;
    /**
     * StripePaymentProvider constructor.
     *
     * @param StripeService $stripeService
     */
    public function __construct(StripeService $stripeService)
    {
        $this->setStripeService($stripeService);
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
        throw $this->createNotYetImplementedException('Stripe payment provider not yet implemented');
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
        throw $this->createNotYetImplementedException('Stripe payment provider not yet implemented');
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
        throw $this->createNotYetImplementedException('Stripe payment provider not yet implemented');
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
        throw $this->createNotYetImplementedException('Stripe payment provider not yet implemented');
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
        throw $this->createNotYetImplementedException('Stripe payment provider not yet implemented');
    }
    /**
     * @param string  $callback
     * @param Request $request
     *
     * @return array
     *
     * @todo implement this
     */
    public function parseCallbackRequest($callback, Request $request)
    {
        return [];
    }
}
