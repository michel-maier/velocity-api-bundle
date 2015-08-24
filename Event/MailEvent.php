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
 * Mail Event.
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
class MailEvent extends Event
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
     * @var array
     */
    protected $options;
    /**
     * @param string $type
     * @param array  $params
     * @param array  $options
     */
    public function __construct($type, array $params = [], array $options = [])
    {
        $this->setType($type);
        $this->setParams($params);
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