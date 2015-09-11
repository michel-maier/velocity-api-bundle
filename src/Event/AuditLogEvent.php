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
 * Audit Log Event.
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
class AuditLogEvent extends Event
{
    /**
     * @var string
     */
    protected $type;
    /**
     * @var string
     */
    protected $contextType;
    /**
     * @var string
     */
    protected $contextId;
    /**
     * @var string
     */
    protected $userId;
    /**
     * @var \DateTime
     */
    protected $date;
    /**
     * @var array
     */
    protected $params;
    /**
     * @param string    $type
     * @param string    $contextType
     * @param string    $contextId
     * @param string    $userId
     * @param \DateTime $date
     * @param array     $params
     */
    public function __construct($type, $contextType, $contextId, $userId, \DateTime $date, array $params = [])
    {
        $this->setType($type);
        $this->setContextType($contextType);
        $this->setContextId($contextId);
        $this->setUserId($userId);
        $this->setDate($date);
        $this->setParams($params);
    }
    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }
    /**
     * @return string
     */
    public function getContextType()
    {
        return $this->contextType;
    }
    /**
     * @return string
     */
    public function getContextId()
    {
        return $this->contextId;
    }
    /**
     * @return string
     */
    public function getUserId()
    {
        return $this->userId;
    }
    /**
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }
    /**
     * @return array
     */
    public function getParams()
    {
        return $this->params;
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
     * @param string $contextType
     *
     * @return $this
     */
    protected function setContextType($contextType)
    {
        $this->contextType = $contextType;

        return $this;
    }
    /**
     * @param string $contextId
     *
     * @return $this
     */
    protected function setContextId($contextId)
    {
        $this->contextId = $contextId;

        return $this;
    }
    /**
     * @param string $userId
     *
     * @return $this
     */
    protected function setUserId($userId)
    {
        $this->userId = $userId;

        return $this;
    }
    /**
     * @param \DateTime $date
     *
     * @return $this
     */
    protected function setDate(\DateTime $date)
    {
        $this->date = $date;

        return $this;
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
}
