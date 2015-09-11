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

/**
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
class PayPalPrepareGetOrderFailedException extends PayPalException
{
    /**
     * @var string
     */
    protected $token;
    /**
     * @var array
     */
    protected $options;
    /**
     * @param string     $token
     * @param array      $options
     * @param \Exception $previous
     */
    public function __construct($token, $options, \Exception $previous)
    {
        parent::__construct(
            sprintf("PayPal get order '%s' preparation failed: %s", $token, $previous->getMessage()),
            $this->buildErrors($previous),
            500,
            $previous
        );

        $this->token = $token;
        $this->options = $options;
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
        return $this->token;
    }
    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
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
