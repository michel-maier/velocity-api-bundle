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
 * Sms Event.
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
class SmsEvent extends Event
{
    /**
     * @var string
     */
    protected $type;
    /**
     * @var array
     */
    protected $params;
    /**
     * @var null|array
     */
    protected $sender;
    /**
     * @var array
     */
    protected $options;
    /**
     * @param string     $type
     * @param array      $params
     * @param null|array $sender
     * @param array      $options
     */
    public function __construct($type, array $params = [], $sender = null, array $options = [])
    {
        $this->setType($type);
        $this->setParams($params);
        $this->setSender($sender);
        $this->setOptions($options);
    }
    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }
    /**
     * @param string $type
     *
     * @return $this
     */
    protected function setType($type)
    {
        $this->type = $type;

        return $this;
    }
    /**
     * @return string
     */
    public function getTemplate()
    {
        return str_replace('.', '/', $this->getType());
    }
    /**
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }
    /**
     * @param array $params
     *
     * @return $this
     */
    protected function setParams(array $params)
    {
        $this->params = $params;

        return $this;
    }
    /**
     * @return array|null
     */
    public function getSender()
    {
        return $this->sender;
    }
    /**
     * @param array|null $sender
     *
     * @return SmsEvent
     */
    protected function setSender($sender)
    {
        $this->sender = $sender;

        return $this;
    }
    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }
    /**
     * @param array $options
     *
     * @return $this
     */
    protected function setOptions(array $options)
    {
        $this->options = $options;

        return $this;
    }
}