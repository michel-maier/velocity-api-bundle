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

/**
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
class PayPalExecuteConfirmOrderFailedException extends PayPalException
{
    /**
     * @var DoExpressCheckoutPaymentReq
     */
    protected $expressCheckoutRequest;
    /**
     * @param DoExpressCheckoutPaymentReq $request
     *
     * @param \Exception                  $previous
     */
    public function __construct(DoExpressCheckoutPaymentReq $request, \Exception $previous)
    {
        // @codingStandardsIgnoreStart
        /** @noinspection PhpUndefinedFieldInspection */
        parent::__construct(
            sprintf(
                "PayPal confirm order '%s' execution failed: %s",
                $request->DoExpressCheckoutPaymentRequest->Token,
                $previous->getMessage()
            ),
            $this->buildErrors($previous),
            500,
            $previous
        );
        // @codingStandardsIgnoreEnd

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
        // @codingStandardsIgnoreLine
        return $this->getExpressCheckoutRequest()->DoExpressCheckoutPaymentRequest->Token;
    }
    /**
     * @return DoExpressCheckoutPaymentReq
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
            ],
        ];
    }
}
