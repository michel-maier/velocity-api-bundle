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
class PayPalException extends \RuntimeException
{
    /**
     * @var array
     */
    protected $errors;
    /**
     * @param string     $message
     * @param array      $errors
     * @param int        $code
     * @param \Exception $previous
     */
    public function __construct($message = null, $errors = [], $code = 500, \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);

        $this->errors = $errors;
    }
    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }
}