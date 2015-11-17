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
        $data += [
            'operationFee' => 0.0,
            'culture'      => 'FR',
            'cardType'     => 'CB_VISA_MASTERCARD',
            'secureMode'   => 'DEFAULT',
        ];

        $data['sellerUserId']   = $this->createUserIfNeeded($data, 'seller');
        $data['buyerUserId']    = $this->createUserIfNeeded($data, 'buyer');
        $data['sellerWalletId'] = $this->createWalletIfNeeded($data, 'seller');
        $data['buyerWalletId']  = $this->createWalletIfNeeded($data, 'buyer');

        $payIn = $this->getMangoPayService()->createPayIn([
            'authorId'         => $data['buyerUserId'],
            'debitedFunds'     => $data['amount'],
            'fees'             => $data['operationFee'],
            'creditedWalletId' => $data['buyerUserId'],
            'returnUrl'        => $data['succeedUrl'],
            'culture'          => $data['culture'],
            'cardType'         => $data['cardType'],
            'secureMode'       => $data['secureMode'],
            'creditedUserId'   => $data['buyerUserId'],
        ]);

        return [
            'buyerWalletId'  => $data['buyerWalletId'],
            'buyerUserId'    => $data['buyerUserId'],
            'sellerWalletId' => $data['sellerWalletId'],
            'sellerUserId'   => $data['sellerUserId'],
            'url'            => $payIn['executionDetails']['redirectURL'],
            'transaction'    => $payIn['id'],
            'correlation'    => null,
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
        $transfer = $this->getMangoPayService()->createTransfer([
            'authorId' => $doc['payment']['params']['buyerUserId'],
            'debitedFunds' => $doc['payment']['amount'],
            'fees' => $feeAmount,
            'debitedWalletId' => $doc['payment']['params']['buyerWalletId'],
            'creditedWalletId' => $doc['payment']['params']['tombolaWalletId'],
            'creditedUserId' => $doc['payment']['params']['sellerUserId'],
        ]);

        $platformFeeRatio = 0.08;
        $mangoPayFeeRatio = 0.04;

        $totalFeeRatio = $platformFeeRatio + $mangoPayFeeRatio;

        $feeAmount = $doc['payment']['amount'] * $totalFeeRatio;

        $mpTransfer = $this->getMangoPayService()->createTransfer([
            'authorId' => $doc['payment']['params']['buyerUserId'],
            'debitedFunds' => $doc['payment']['amount'],
            'fees' => $feeAmount,
            'debitedWalletId' => $doc['payment']['params']['buyerWalletId'],
            'creditedWalletId' => $doc['payment']['params']['tombolaWalletId'],
            'creditedUserId' => $doc['payment']['params']['sellerUserId'],
        ]);

        $ampTransfer = $this->arrayize($mpTransfer);

        if ('SUCCEEDED' !== $ampTransfer['status']) {
            $this->getRepository()->setDocumentProperties($id, [
                'payment.params.transfer' => $ampTransfer,
                'status' => 'failed',
            ]);

            $this->event('failed', $this->get($id));

            return [
                'token' => $doc['token'],
                'status' => 'failed',
                'payment' => [
                    'failedCallback' => $doc['callbacks']['failure'],
                ],
            ];
        }

        $this->getRepository()->setDocumentProperties($id, [
            'payment.params.transfer' => $ampTransfer,
            'status' => 'confirmed',
        ]);

        $this->event('confirmed', $this->get($id));

        return [
            'token' => $doc['token'],
            'status' => 'succeed',
            'payment' => [
                'transferId' => $mpTransfer['id'],
                'transferredAmount' => (double)$doc['payment']['amount'] - (double)$feeAmount,
                'succeedCallback' => $doc['callbacks']['success'],
                'cancelledCallback' => $doc['callbacks']['cancel'],
                'failedCallback' => $doc['callbacks']['failure'],
            ],
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
    /**
     * @param string  $callback
     * @param Request $request
     *
     * @return array
     */
    public function parseCallbackRequest($callback, Request $request)
    {
        switch ($callback) {
            default:
                return [];
        }
    }
    /**
     * @param array  $data
     * @param string $prefix
     *
     * @return string
     */
    protected function createUserIfNeeded(array $data, $prefix)
    {
        if (isset($data[$prefix.'UserId'])) {
            return $data[$prefix.'UserId'];
        }

        $user = $this->getMangoPayService()->createUser([
            'firstName'          => $data[$prefix.'FirstName'],
            'lastName'           => $data[$prefix.'LastName'],
            'email'              => $data[$prefix.'Email'],
            'birthday'           => $data[$prefix.'BirthDay'],
            'nationality'        => $data[$prefix.'Nationality'],
            'countryOfResidence' => $data[$prefix.'CountryOfResidence'],
        ]);

        return $user['id'];
    }
    /**
     * @param array  $data
     * @param string $prefix
     *
     * @return string
     */
    protected function createWalletIfNeeded(array $data, $prefix)
    {
        if (isset($data[$prefix.'WalletId'])) {
            return $data[$prefix.'WalletId'];
        }

        $wallet = $this->getMangoPayService()->createWallet([
            'currency'    => isset($data[$prefix.'Currency']) ? $data[$prefix.'Currency'] : $data['currency'],
            'description' => isset($data[$prefix.'WalletDescription']) ? $data[$prefix.'WalletDescription'] : 'User wallet',
            'owners'      => [$data[$prefix.'UserId']],
        ]);

        return $wallet['id'];
    }
}
