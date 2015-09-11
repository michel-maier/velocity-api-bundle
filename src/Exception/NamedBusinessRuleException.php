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

use RuntimeException;

/**
 * BusinessRuleException.
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
class NamedBusinessRuleException extends RuntimeException
{
    /**
     * @var string
     */
    protected $id;
    /**
     * @var string
     */
    protected $name;
    /**
     * @var BusinessRuleException
     */
    protected $exception;
    /**
     * @param string                $id
     * @param string                $name
     * @param BusinessRuleException $previous
     */
    public function __construct($id, $name, BusinessRuleException $previous)
    {
        parent::__construct(
            sprintf("Business rule #%s '%s' error: %s", $id, $name, $previous->getMessage()),
            $previous->getCode()
        );

        // do not set a "previous exception" because Symfony 2 Console is not behaving the right way
        $this->setBusinessRuleException($previous);
    }
    /**
     * @return BusinessRuleException
     */
    public function getBusinessRuleException()
    {
        return $this->getPrevious();
    }
    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }
    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
    /**
     * @param string $id
     *
     * @return $this
     */
    protected function setId($id)
    {
        $this->id = $id;

        return $this;
    }
    /**
     * @param string $name
     * @return $this
     */
    protected function setName($name)
    {
        $this->name = $name;

        return $this;
    }
    /**
     * @param BusinessRuleException $e
     *
     * @return $this
     */
    protected function setBusinessRuleException(BusinessRuleException $e)
    {
        $this->exception = $e;

        return $this;
    }
}
