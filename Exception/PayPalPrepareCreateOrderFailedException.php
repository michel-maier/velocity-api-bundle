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
class PayPalPrepareCreateOrderFailedException extends PayPalException
{
    /**
     * @var array
     */
    protected $data;
    /**
     * @var array
     */
    protected $options;
    /**
     * @param array      $data
     * @param array      $options
     * @param \Exception $previous
     */
    public function __construct($data, $options = [], \Exception $previous)
    {
        parent::__construct(
            sprintf("PayPal create order preparation failed: %s", $previous->getMessage()),
            $this->buildErrors($previous),
            500,
            $previous
        );

        $this->data = $data;
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
     * @return array
     */
    public function getData()
    {
        return $this->data;
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
            ]
        ];
    }
}