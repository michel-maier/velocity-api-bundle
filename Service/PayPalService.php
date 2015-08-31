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

use PayPal\PayPalAPI\SetExpressCheckoutReq;
use PayPal\CoreComponentTypes\BasicAmountType;
use PayPal\EBLBaseComponents\PaymentDetailsType;
use PayPal\PayPalAPI\DoExpressCheckoutPaymentReq;
use PayPal\PayPalAPI\GetExpressCheckoutDetailsReq;
use Velocity\Bundle\ApiBundle\Traits\ServiceTrait;
use PayPal\PayPalAPI\SetExpressCheckoutRequestType;
use PayPal\EBLBaseComponents\PaymentDetailsItemType;
use PayPal\Service\PayPalAPIInterfaceServiceService;
use PayPal\PayPalAPI\SetExpressCheckoutResponseType;
use Velocity\Bundle\ApiBundle\Traits\LoggerAwareTrait;
use PayPal\PayPalAPI\DoExpressCheckoutPaymentRequestType;
use PayPal\PayPalAPI\GetExpressCheckoutDetailsRequestType;
use PayPal\PayPalAPI\DoExpressCheckoutPaymentResponseType;
use PayPal\PayPalAPI\GetExpressCheckoutDetailsResponseType;
use PayPal\EBLBaseComponents\SetExpressCheckoutRequestDetailsType;
use Velocity\Bundle\ApiBundle\Exception\PayPalGetOrderFailedException;
use PayPal\EBLBaseComponents\DoExpressCheckoutPaymentRequestDetailsType;
use Velocity\Bundle\ApiBundle\Exception\PayPalCreateOrderFailedException;
use Velocity\Bundle\ApiBundle\Exception\PayPalConfirmOrderFailedException;
use Velocity\Bundle\ApiBundle\Exception\PayPalExecuteGetOrderFailedException;
use Velocity\Bundle\ApiBundle\Exception\PayPalPrepareGetOrderFailedException;
use Velocity\Bundle\ApiBundle\Exception\PayPalPrepareCreateOrderFailedException;
use Velocity\Bundle\ApiBundle\Exception\PayPalPrepareConfirmOrderFailedException;
use Velocity\Bundle\ApiBundle\Exception\PayPalExecuteCreateOrderFailedException;
use Velocity\Bundle\ApiBundle\Exception\PayPalExecuteConfirmOrderFailedException;

/**
 * PayPal Service.
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
class PayPalService
{
    use ServiceTrait;
    /**
     * Default PayPal API version
     */
    const DEFAULT_VERSION = '104.0';
    /**
     * Default PayPal Environment (live=production, sandbox=integration)
     */
    const DEFAULT_ENVIRONMENT = 'live';
    /**
     * Default PayPal Currency
     */
    const DEFAULT_CURRENCY = 'EUR';
    /**
     * Default PayPal Payment Screen URL for Sandbox environment
     */
    const DEFAULT_SANDBOX_PAYMENT_URL_PATTERN = 'https://www.sandbox.paypal.com/cgi-bin/webscr?cmd=_express-checkout&token={token}';
    /**
     * Default PayPal Payment Screen URL for Live environment
     */
    const DEFAULT_LIVE_PAYMENT_URL_PATTERN    = 'https://www.paypal.com/cgi-bin/webscr?cmd=_express-checkout&token={token}';

    use ServiceTrait;
    use LoggerAwareTrait;
    /**
     * @param string $version
     *
     * @return $this
     */
    public function setVersion($version)
    {
        return $this->setParameter('version', $version);
    }
    /**
     * @return string
     */
    public function getVersion()
    {
        return $this->getParameter('version', static::DEFAULT_VERSION);
    }
    /**
     * @return string
     */
    public function getCurrency()
    {
        return $this->getParameter('currency', static::DEFAULT_CURRENCY);
    }
    /**
     * @param string $currency
     *
     * @return $this
     */
    public function setCurrency($currency)
    {
        return $this->setParameter('currency', $currency);
    }
    /**
     * @return string
     */
    public function getEnvironment()
    {
        return $this->getParameter('environment', static::DEFAULT_ENVIRONMENT);
    }
    /**
     * @param string $environment
     *
     * @return $this
     */
    public function setEnvironment($environment)
    {
        return $this->setParameter('environment', $environment);
    }
    /**
     * @return string
     */
    public function getPaymentUrlPattern()
    {
        $value = $this->getParameterIfExists('paymentUrlPattern');

        if (null === $value) {
            switch ($this->getEnvironment()) {
                case 'sandbox':
                    return static::DEFAULT_SANDBOX_PAYMENT_URL_PATTERN;
                case 'live':
                    return static::DEFAULT_LIVE_PAYMENT_URL_PATTERN;
                default:
                    throw $this->createException(500, "Unsupported PayPal environment '%s'", $this->getEnvironment());
            }
        }

        return $value;
    }
    /**
     * @return PayPalAPIInterfaceServiceService
     */
    public function getPayPal()
    {
        return $this->getService('payPal');
    }
    /**
     * @param PayPalAPIInterfaceServiceService $payPal
     *
     * @return $this
     */
    public function setPayPal(PayPalAPIInterfaceServiceService $payPal)
    {
        return $this->setService('payPal', $payPal);
    }
    /**
     * @param array $data
     * @param array $options
     *
     * @return array
     *
     * @throws \Exception
     */
    public function createOrder($data, $options = [])
    {
        try {
            $total = 0.0;
            $itemTotal = 0.0;
            $taxTotal = 0.0;
            $action = 'Sale';

            $version = $this->getVersion();

            if (!isset($data['successUrl'])) {
                if (!isset($data['returnUrl'])) {
                    throw $this->createException(412, "Missing success or return url");
                }
                $data['successUrl'] = $data['returnUrl'];
            }
            if (!isset($data['cancelUrl'])) {
                if (!isset($data['returnUrl'])) {
                    throw $this->createException(412, "Missing cancel or return url");
                }
                $data['cancelUrl'] = $data['returnUrl'];
            }
            $currency = isset($data['currency']) ? (string) $data['currency'] : $this->getCurrency();
            $successUrl = str_replace('{result}', 'success', (string) $data['successUrl']);
            $cancelUrl = str_replace('{result}', 'cancel', (string) $data['cancelUrl']);

            $paymentDetails = new PaymentDetailsType();

            if (isset($data['items']) && is_array($data['items']) && count($data['items'])) {
                foreach ($data['items'] as $i => $item) {
                    $itemDetails = new PaymentDetailsItemType($item);
                    // @codingStandardsIgnoreStart
                    $itemDetails->Name = (string) $item['name'] ?: sprintf('Item %s', is_numeric($i) ? ($i + 1) : $i);
                    $itemDetails->Number = (string) $item['number'] ?: null;
                    $itemDetails->Amount = (string) $item['amount'] ?: '0.0';
                    $itemDetails->Quantity = (string) $item['quantity'] ?: '1';
                    $itemDetails->Description = (string) $item['description'] ?: null;
                    $itemDetails->PromoCode = (string) $item['code'] ?: null;
                    $itemDetails->ProductCategory = (string) $item['category'] ?: null;
                    // @codingStandardsIgnoreEnd
                    if (isset($item['tax']) && $item['tax']) {
                        $itemDetailsTax = new BasicAmountType();
                        // @codingStandardsIgnoreStart
                        $itemDetailsTax->currencyID = $currency;
                        $itemDetailsTax->value = (string) $item['tax'];

                        $itemDetails->Tax = $itemDetailsTax;
                        // @codingStandardsIgnoreEnd

                        $total += (double) $itemDetailsTax->value;
                        $taxTotal += (double) $itemDetailsTax->value;
                    }
                    // @codingStandardsIgnoreStart
                    $paymentDetails->PaymentDetailsItem[] = $itemDetails;

                    $total += (double) $itemDetails->Amount;
                    $itemTotal += (double) $itemDetails->Amount;
                    // @codingStandardsIgnoreEnd
                }
            }

            // @codingStandardsIgnoreStart
            $orderTotal = new BasicAmountType();
            $orderTotal->currencyID = $currency;
            $orderTotal->value = $total;
            $iTotal = new BasicAmountType();
            $iTotal->currencyID = $currency;
            $iTotal->value = $itemTotal;

            $tTotal = new BasicAmountType();
            $tTotal->currencyID = $currency;
            $tTotal->value = $taxTotal;

            $paymentDetails->OrderTotal = $orderTotal;
            $paymentDetails->PaymentAction = $action;
            $paymentDetails->ItemTotal = $iTotal;
            $paymentDetails->TaxTotal = $tTotal;

            if (isset($data['description']) && $data['description']) {
                $paymentDetails->OrderDescription = (string) $data['description'];
            }

            if (isset($data['data']) && $data['data']) {
                $paymentDetails->Custom = (string) $data['data'];
            }

            if (isset($data['invoiceId']) && $data['invoiceId']) {
                $paymentDetails->InvoiceID = (string) $data['invoiceId'];
            }

            $setECReqDetails = new SetExpressCheckoutRequestDetailsType();
            $setECReqDetails->PaymentDetails[] = $paymentDetails;
            $setECReqDetails->CancelURL = $cancelUrl;
            $setECReqDetails->ReturnURL = $successUrl;
            $setECReqDetails->NoShipping = 1;

            $setECReqType = new SetExpressCheckoutRequestType();
            $setECReqType->Version = $version;
            $setECReqType->SetExpressCheckoutRequestDetails = $setECReqDetails;

            $setECReq = new SetExpressCheckoutReq();
            $setECReq->SetExpressCheckoutRequest = $setECReqType;
            // @codingStandardsIgnoreEnd
        } catch (\Exception $e) {
            throw new PayPalPrepareCreateOrderFailedException($data, $options, $e);
        }

        try {
            /** @var SetExpressCheckoutResponseType $payPalResponse */
            $payPalResponse = $this->getPayPal()->SetExpressCheckout($setECReq);
        } catch (\Exception $e) {
            throw new PayPalExecuteCreateOrderFailedException($setECReq, $e);
        }

        // @codingStandardsIgnoreLine
        if ('Success' !== $payPalResponse->Ack) {
            throw new PayPalCreateOrderFailedException($setECReq, $payPalResponse);
        }

        unset($options);

        // @codingStandardsIgnoreStart
        return [
            'token'         => (string) $payPalResponse->Token,
            'timestamp'     => (string) $payPalResponse->Timestamp,
            'correlationId' => (string) $payPalResponse->CorrelationID,
            'version'       => (string) $payPalResponse->Version,
            'build'         => (string) $payPalResponse->Build,
            'paymentUrl'    => $this->getPaymentUrl((string) $payPalResponse->Token),
        ];
        // @codingStandardsIgnoreEnd
    }
    /**
     * @param string $token
     * @param array  $options
     *
     * @return array
     *
     * @throws \Exception
     */
    public function getOrder($token, $options = [])
    {
        try {
            $version = $this->getVersion();

            // @codingStandardsIgnoreStart
            $getExpressCheckoutDetailsRequest = new GetExpressCheckoutDetailsRequestType($token);
            $getExpressCheckoutDetailsRequest->Version = $version;

            $getExpressCheckoutReq = new GetExpressCheckoutDetailsReq();
            $getExpressCheckoutReq->GetExpressCheckoutDetailsRequest = $getExpressCheckoutDetailsRequest;
            // @codingStandardsIgnoreEnd
        } catch (\Exception $e) {
            throw new PayPalPrepareGetOrderFailedException($token, $options, $e);
        }

        /** @var GetExpressCheckoutDetailsResponseType $payPalResponse */
        try {
            $payPalResponse = $this->getPayPal()->GetExpressCheckoutDetails($getExpressCheckoutReq);
        } catch (\Exception $e) {
            throw new PayPalExecuteGetOrderFailedException($getExpressCheckoutReq, $e);

        }

        // @codingStandardsIgnoreLine
        if ('Success' !== $payPalResponse->Ack) {
            throw new PayPalGetOrderFailedException($getExpressCheckoutReq, $payPalResponse);
        }

        unset($options);

        /** @noinspection PhpUndefinedFieldInspection */
        // @codingStandardsIgnoreStart
        return [
            'token'               => $token,
            'status'              => $payPalResponse->GetExpressCheckoutDetailsResponseDetails->CheckoutStatus,
            'custom'              => $payPalResponse->GetExpressCheckoutDetailsResponseDetails->Custom,
            'invoiceId'           => $payPalResponse->GetExpressCheckoutDetailsResponseDetails->InvoiceID,
            'contactPhone'        => $payPalResponse->GetExpressCheckoutDetailsResponseDetails->ContactPhone,
            'billingAgreement'    => $payPalResponse->GetExpressCheckoutDetailsResponseDetails->BillingAgreementAcceptedStatus,
            'note'                => $payPalResponse->GetExpressCheckoutDetailsResponseDetails->Note,
            'checkoutStatus'      => $payPalResponse->GetExpressCheckoutDetailsResponseDetails->CheckoutStatus,
            'giftMessage'         => $payPalResponse->GetExpressCheckoutDetailsResponseDetails->GiftMessage,
            'buyerMarketingEmail' => $payPalResponse->GetExpressCheckoutDetailsResponseDetails->BuyerMarketingEmail,
        ];
        // @codingStandardsIgnoreEnd
    }
    /**
     * @param string $token
     * @param array  $data
     * @param array  $options
     *
     * @return array
     *
     * @throws \Exception
     */
    public function confirmOrder($token, $data = [], $options = [])
    {
        try {
            $action = 'Sale';

            $payerId = $data['payerId'];
            $notifyUrl = isset($data['notifyUrl']) ? $data['notifyUrl'] : null;

            $version = $this->getVersion();

            // @codingStandardsIgnoreStart
            $paymentDetails = new PaymentDetailsType();
            $paymentDetails->PaymentAction = $action;
            $paymentDetails->NotifyURL = $notifyUrl;

            $DoECRequestDetails = new DoExpressCheckoutPaymentRequestDetailsType();
            $DoECRequestDetails->PayerID = $payerId;
            $DoECRequestDetails->Token = $token;

            $orderTotal = new BasicAmountType();
            $orderTotal->currencyID = 'EUR'; // @todo remove that hardcoded string
            $orderTotal->value = $data['amount'];
            $paymentDetails->OrderTotal = $orderTotal;

            $DoECRequestDetails->PaymentDetails[] = $paymentDetails;

            $DoECRequest = new DoExpressCheckoutPaymentRequestType();
            $DoECRequest->Version = $version;
            $DoECRequest->DoExpressCheckoutPaymentRequestDetails = $DoECRequestDetails;

            $DoECReq = new DoExpressCheckoutPaymentReq();
            $DoECReq->DoExpressCheckoutPaymentRequest = $DoECRequest;
            // @codingStandardsIgnoreEnd
        } catch (\Exception $e) {
            throw new PayPalPrepareConfirmOrderFailedException($token, $data, $options, $e);
        }

        /** @var DoExpressCheckoutPaymentResponseType $payPalResponse */
        // @codingStandardsIgnoreStart
        try {
            $payPalResponse = $this->getPayPal()->DoExpressCheckoutPayment($DoECReq);
        } catch (\Exception $e) {
            throw new PayPalExecuteConfirmOrderFailedException($DoECReq, $e);
        }
        // @codingStandardsIgnoreEnd

        // @codingStandardsIgnoreStart
        if ('Success' !== $payPalResponse->Ack) {
            throw new PayPalConfirmOrderFailedException($DoECReq, $payPalResponse);
        }
        // @codingStandardsIgnoreEnd

        unset($options);

        /** @noinspection PhpUndefinedFieldInspection */
        // @codingStandardsIgnoreStart
        return [
            'token' => $token,
            'billingAgreementId' => $payPalResponse->DoExpressCheckoutPaymentResponseDetails->BillingAgreementID,
            'note'               => $payPalResponse->DoExpressCheckoutPaymentResponseDetails->Note,
            'transactionId'      => $payPalResponse->PaymentInfo->TransactionID,
            'receiptId'          => $payPalResponse->PaymentInfo->ReceiptID,
            'transactionType'    => $payPalResponse->PaymentInfo->TransactionType,
            'paymentType'        => $payPalResponse->PaymentInfo->PaymentType,
            'paymentDate'        => $payPalResponse->PaymentInfo->PaymentDate,
            'grossAmount'        => $payPalResponse->PaymentInfo->GrossAmount,
            'feeAmount'          => $payPalResponse->PaymentInfo->FeeAmount,
            'taxAmount'          => $payPalResponse->PaymentInfo->TaxAmount,
            'exchangeRate'       => $payPalResponse->PaymentInfo->ExchangeRate,
            'paymentStatus'      => $payPalResponse->PaymentInfo->PaymentStatus,
            'pendingReason'      => $payPalResponse->PaymentInfo->PendingReason,
            'shippingMethod'     => $payPalResponse->PaymentInfo->ShippingMethod,
            'subject'            => $payPalResponse->PaymentInfo->Subject,
            'storeId'            => $payPalResponse->PaymentInfo->StoreID,
            'terminalId'         => $payPalResponse->PaymentInfo->TerminalID,
            'amount'             => $payPalResponse->Amount,
        ];
        // @codingStandardsIgnoreEnd
    }
    /**
     * @param string $token
     *
     * @return string
     */
    protected function getPaymentUrl($token)
    {
        return str_replace('{token}', $token, $this->getPaymentUrlPattern());
    }
}
