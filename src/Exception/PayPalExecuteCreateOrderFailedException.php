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

/**
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
class PayPalExecuteCreateOrderFailedException extends PayPalException
{
    /**
     * @var SetExpressCheckoutReq
     */
    protected $expressCheckoutRequest;
    /**
     * @param SetExpressCheckoutReq $request
     *
     * @param \Exception            $previous
     */
    public function __construct(SetExpressCheckoutReq $request, \Exception $previous)
    {
        parent::__construct(
            sprintf("PayPal create order execution failed: %s", $previous->getMessage()),
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
     * @return SetExpressCheckoutReq
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
