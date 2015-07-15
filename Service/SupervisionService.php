<?php

namespace Velocity\Bundle\ApiBundle\Service;

use DateTime;
use Velocity\Bundle\ApiBundle\Traits\ServiceTrait;
use Symfony\Component\Security\Core\SecurityContextInterface;

class SupervisionService
{
    use ServiceTrait;
    /**
     * @param SecurityContextInterface $securityContext
     */
    public function __construct(SecurityContextInterface $securityContext)
    {
        $this->setSecurityContext($securityContext);
    }
    /**
     * @param SecurityContextInterface $securityContextInterface
     *
     * @return $this
     */
    public function setSecurityContext(SecurityContextInterface $securityContextInterface)
    {
        return $this->setService('securityContext', $securityContextInterface);
    }
    /**
     * @return SecurityContextInterface
     */
    public function getSecurityContext()
    {
        return $this->getService('securityContext');
    }
    /**
     * @return array
     */
    public function getPingInfos()
    {
        return [
            'date' => new Datetime(),
            'startDuration' => defined('APP_TIME_START') ? (microtime(true) - constant('APP_TIME_START')) : null,
            'php' => [
                'version' => PHP_VERSION,
                'os' => PHP_OS,
                'versionId' => PHP_VERSION_ID,
            ],
            'hostName' => gethostname(),
        ];
    }
    /**
     * @return array
     */
    public function getIdentityInfos()
    {
        return $this->getSecurityContext()->getToken();
    }
}