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

/**
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
class PayPalExecuteGetOrderFailedException extends PayPalException
{
    /**
     * @var GetExpressCheckoutDetailsReq
     */
    protected $expressCheckoutRequest;
    /**
     * @param GetExpressCheckoutDetailsReq $request
     *
     * @param \Exception                   $previous
     */
    public function __construct(GetExpressCheckoutDetailsReq $request, \Exception $previous)
    {
        /** @noinspection PhpUndefinedFieldInspection */
        parent::__construct(
            sprintf(
                "PayPal get order '%s' execution failed: %s",
                $request->GetExpressCheckoutDetailsRequest->Token,
                $previous->getMessage()
            ),
            $this->buildErrors($previous),
            500,
            $previous
        );

        $this->expressCheckoutRequest = $request;
    }
    /**
     * @return \Exception
     */
    public function getPayPalException()
    {
        return $this->getPrevious();
    }
    /**
     * @return string
     */
    public function getToken()
    {
        /** @noinspection PhpUndefinedFieldInspection */
        return $this->getExpressCheckoutRequest()->GetExpressCheckoutDetailsRequest->Token;
    }
    /**
     * @return GetExpressCheckoutDetailsReq
     */
    public function getExpressCheckoutRequest()
    {
        return $this->expressCheckoutRequest;
    }
    /**
     * @param \Exception $e
     *
     * @return array
     */
    protected function buildErrors(\Exception $e)
    {
        return [
            [
                'ErrorCode'       => $e->getCode(),
                'ShortMessage'    => $e->getMessage(),
                'LongMessage'     => $e->getMessage(),
                'SeverityCode'    => 'error',
                'ErrorParameters' => [],
            ]
        ];
    }
}
