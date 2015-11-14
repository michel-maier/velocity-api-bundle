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
use Velocity\Bundle\ApiBundle\Service\PayPalService;
use Velocity\Bundle\ApiBundle\PaymentProviderInterface;

/**
 * PayPal Payment Provider.
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
class PayPalPaymentProvider implements PaymentProviderInterface
{
    use ServiceTrait;
    use ServiceAware\PayPalServiceAwareTrait;
    /**
     * PayPalPaymentProvider constructor.
     *
     * @param PayPalService $payPalService
     */
    public function __construct(PayPalService $payPalService)
    {
        $this->setPayPalService($payPalService);
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
        $order = [
            'successUrl'  => $data['succeedUrl'],
            'cancelUrl'   => $data['canceledUrl'],
            'currency'    => $data['currency'],
            'name'        => $data['name'],
            'amount'      => $data['amount'],
            'description' => $data['description'],
            'items'       => [
                [
                    'name'        => $data['name'],
                    'description' => $data['description'],
                    'amount'      => $data['amount'],
                    'currency'    => $data['currency'],
                ],
            ],
        ];

        $payPalOrder = $this->getPayPalService()->createOrder($order, $options);

        return [
            'url'         => $payPalOrder['paymentUrl'],
            'transaction' => $payPalOrder['token'],
            'correlation' => $payPalOrder['correlationId'],
        ];
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
        if (!isset($data['payerId'])) {
            throw $this->createRequiredException('Payer id is missing');
        }

        return $this->getPayPalService()->confirmOrder($id, ['payerId' => $data['payerId']], $options);
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
        if (!isset($data['payerId'])) {
            throw $this->createRequiredException('Payer id is missing');
        }

        return $this->getPayPalService()->failOrder($id, ['payerId' => $data['payerId']], $options);
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
        if (!isset($data['payerId'])) {
            throw $this->createRequiredException('Payer id is missing');
        }

        return $this->getPayPalService()->cancelOrder($id, ['payerId' => $data['payerId']], $options);
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
        return $this->getPayPalService()->getOrder($id, $options);
    }
    /**
     * @param string  $callback
     * @param Request $request
     *
     * @return array
     *
     * @throws \Exception
     */
    public function parseCallbackRequest($callback, Request $request)
    {
        switch ($callback) {
            case 'paid':
                if (!$request->query->has('token')) {
                    throw $this->createMalformedException('Malformed callback parameters (#1)');
                }
                if (!$request->query->has('PayerID')) {
                    throw $this->createMalformedException('Malformed callback parameters (#2)');
                }

                return ['transaction' => $request->query->get('token'), 'payer' => $request->query->get('PayerID')];
            default:
                return [];
        }
    }
}
