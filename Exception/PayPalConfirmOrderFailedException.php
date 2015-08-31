<?php

/*
 * This file is part of the VELOCITY package.
 *
 * (c) PHPPRO <opensource@phppro.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Velocity\Bundle\ApiBundle\Exception;

use PayPal\PayPalAPI\DoExpressCheckoutPaymentReq;
use PayPal\PayPalAPI\DoExpressCheckoutPaymentResponseType;

/**
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
class PayPalConfirmOrderFailedException extends PayPalException
{
    /**
     * @var DoExpressCheckoutPaymentReq
     */
    protected $expressCheckoutRequest;
    /**
     * @var DoExpressCheckoutPaymentResponseType
     */
    protected $expressCheckoutResponse;
    /**
     * @param DoExpressCheckoutPaymentReq          $request
     * @param DoExpressCheckoutPaymentResponseType $response
     */
    public function __construct(DoExpressCheckoutPaymentReq $request, DoExpressCheckoutPaymentResponseType $response)
    {
        // @codingStandardsIgnoreStart
        /** @noinspection PhpUndefinedFieldInspection */
        parent::__construct(
            sprintf(
                "PayPal confirm order '%s' failed with status : %s",
                $request->DoExpressCheckoutPaymentRequest->Token,
                $response->Ack
            ),
            $this->buildErrors($response)
        );
        // @codingStandardsIgnoreEnd

        $this->expressCheckoutRequest = $request;
        $this->expressCheckoutResponse = $response;
    }
    /**
     * @return string
     */
    public function getResponseStatus()
    {
        // @codingStandardsIgnoreLine
        return $this->getExpressCheckoutResponse()->Ack;
    }
    /**
     * @return DoExpressCheckoutPaymentReq
     */
    public function getExpressCheckoutRequest()
    {
        return $this->expressCheckoutRequest;
    }
    /**
     * @return DoExpressCheckoutPaymentResponseType
     */
    public function getExpressCheckoutResponse()
    {
        return $this->expressCheckoutResponse;
    }
    /**
     * @param DoExpressCheckoutPaymentResponseType $response
     *
     * @return array
     */
    protected function buildErrors(DoExpressCheckoutPaymentResponseType $response)
    {
        $errors = [];

        // @codingStandardsIgnoreLine
        foreach ($response->Errors as $error) {
            $errors[] = (array) $error;
        }

        return $errors;
    }
}
