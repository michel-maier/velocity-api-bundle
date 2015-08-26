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

use PayPal\PayPalAPI\SetExpressCheckoutReq;
use PayPal\PayPalAPI\SetExpressCheckoutResponseType;

/**
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
class PayPalCreateOrderFailedException extends PayPalException
{
    /**
     * @var SetExpressCheckoutReq
     */
    protected $expressCheckoutRequest;
    /**
     * @var SetExpressCheckoutResponseType
     */
    protected $expressCheckoutResponse;
    /**
     * @param SetExpressCheckoutReq          $request
     * @param SetExpressCheckoutResponseType $response
     */
    public function __construct(SetExpressCheckoutReq $request, SetExpressCheckoutResponseType $response)
    {
        parent::__construct(
            sprintf("PayPal create order failed with status : %s", $response->Ack),
            $this->buildErrors($response)
        );

        $this->expressCheckoutRequest = $request;
        $this->expressCheckoutResponse = $response;
    }
    /**
     * @return string
     */
    public function getResponseStatus()
    {
        return $this->getExpressCheckoutResponse()->Ack;
    }
    /**
     * @return SetExpressCheckoutReq
     */
    public function getExpressCheckoutRequest()
    {
        return $this->expressCheckoutRequest;
    }
    /**
     * @return SetExpressCheckoutResponseType
     */
    public function getExpressCheckoutResponse()
    {
        return $this->expressCheckoutResponse;
    }
    /**
     * @param SetExpressCheckoutResponseType $response
     *
     * @return array
     */
    protected function buildErrors(SetExpressCheckoutResponseType $response)
    {
        $errors = [];

        foreach ($response->Errors as $error) {
            $errors[] = (array) $error;
        }

        return $errors;
    }
}
