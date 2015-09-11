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
 * ImportException.
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
class ImportException extends \RuntimeException
{
    /**
     * @var array
     */
    protected $errors;
    /**
     * Construct the exception
     *
     * @param array $errors
     */
    public function __construct(array $errors = [])
    {
        parent::__construct('Failed Import', 412);

        $this->errors = $errors;
    }
    /**
     * Return the errors.
     *
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }
}
