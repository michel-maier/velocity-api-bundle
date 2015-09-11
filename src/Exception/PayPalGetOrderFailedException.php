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

use PayPal\PayPalAPI\GetExpressCheckoutDetailsReq;
use PayPal\PayPalAPI\GetExpressCheckoutDetailsResponseType;

/**
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
class PayPalGetOrderFailedException extends PayPalException
{
    /**
     * @var GetExpressCheckoutDetailsReq
     */
    protected $expressCheckoutRequest;
    /**
     * @var GetExpressCheckoutDetailsResponseType
     */
    protected $expressCheckoutResponse;
    /**
     * @param GetExpressCheckoutDetailsReq          $request
     * @param GetExpressCheckoutDetailsResponseType $response
     */
    public function __construct(GetExpressCheckoutDetailsReq $request, GetExpressCheckoutDetailsResponseType $response)
    {
        // @codingStandardsIgnoreStart
        /** @noinspection PhpUndefinedFieldInspection */
        parent::__construct(
            sprintf(
                "PayPal get order '%s' failed with status : %s",
                $request->GetExpressCheckoutDetailsRequest->Token,
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
     * @return GetExpressCheckoutDetailsReq
     */
    public function getExpressCheckoutRequest()
    {
        return $this->expressCheckoutRequest;
    }
    /**
     * @return GetExpressCheckoutDetailsResponseType
     */
    public function getExpressCheckoutResponse()
    {
        return $this->expressCheckoutResponse;
    }
    /**
     * @param GetExpressCheckoutDetailsResponseType $response
     *
     * @return array
     */
    protected function buildErrors(GetExpressCheckoutDetailsResponseType $response)
    {
        $errors = [];

        // @codingStandardsIgnoreLine
        foreach ($response->Errors as $error) {
            $errors[] = (array) $error;
        }

        return $errors;
    }
}
