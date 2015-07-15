<?php

/*
 * This file is part of the VELOCITY package.
 *
 * (c) PHPPRO <opensource@phppro.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Velocity\Bundle\ApiBundle\Security;

use Symfony\Component\Security\Core\Authentication\Token\AbstractToken;

/**
 * API Unauthenticated User Token.
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
class ApiUnauthenticatedUserToken extends AbstractToken
{
    /**
     * @var array
     */
    protected $clientTokenInfos;
    /**
     * @var array
     */
    protected $userTokenInfos;
    /**
     * @var array|null
     */
    protected $impersonatedUserInfos;
    /**
     * @param array $infos
     */
    public function __construct(array $infos)
    {
        parent::__construct([]);

        $this->clientTokenInfos      = isset($infos['client']['id']) ? $infos['client'] : null;
        $this->userTokenInfos        = isset($infos['user']['id'])   ? $infos['user'] : null;
        $this->impersonatedUserInfos = isset($infos['sudo']['id'])   ? $infos['sudo'] : null;

        $this->setUser(isset($this->userTokenInfos['id']) ? $this->userTokenInfos['id'] : 'unknown');
    }
    /**
     * @return array
     */
    public function getClientTokenInfos()
    {
        return $this->clientTokenInfos;
    }
    /**
     * @return array
     */
    public function getUserTokenInfos()
    {
        return $this->userTokenInfos;
    }
    /**
     * @return bool
     */
    public function isImpersonating()
    {
        return null !== $this->impersonatedUserInfos && isset($this->impersonatedUserInfos['id']);
    }
    /**
     * @return array|null
     */
    public function getImpersonatedUserInfos()
    {
        return $this->impersonatedUserInfos;
    }
    /**
     * Returns the user credentials.
     *
     * @return mixed The user credentials
     */
    public function getCredentials()
    {
        return '';
    }
}