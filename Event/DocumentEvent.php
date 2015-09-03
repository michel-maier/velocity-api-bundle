<?php

/*
 * This file is part of the VELOCITY package.
 *
 * (c) PHPPRO <opensource@phppro.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Velocity\Bundle\ApiBundle\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * Document Event.
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
class DocumentEvent extends Event
{
    /**
     * @var object|array
     */
    protected $data;
    /**
     * @var array
     */
    protected $context;
    /**
     * @param object|array $data
     * @param array        $context
     */
    public function __construct($data, array $context = [])
    {
        $this->setData($data);
        $this->setContext($context);
    }
    /**
     * @return array|object
     */
    public function getData()
    {
        return $this->data;
    }
    /**
     * @param array|object $data
     *
     * @return $this
     */
    protected function setData($data)
    {
        $this->data = $data;

        return $this;
    }
    /**
     * @return array
     */
    public function getContext()
    {
        return $this->context;
    }
    /**
     * @param array $context
     *
     * @return DocumentEvent
     */
    protected function setContext($context)
    {
        $this->context = $context;

        return $this;
    }
    /**
     * @param string     $key
     * @param null|mixed $defaultValue
     *
     * @return mixed
     */
    public function getContextVariable($key, $defaultValue = null)
    {
        if (!isset($this->context[$key])) {
            return $defaultValue;
        }

        return $this->context[$key];
    }
}
