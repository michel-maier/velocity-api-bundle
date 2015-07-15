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
 * API Authenticated User Token.
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
class ApiAuthenticatedUserToken extends AbstractToken
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
     * @param array   $clientTokenInfos
     * @param array   $userTokenInfos
     * @param ApiUser $user
     */
    public function __construct(array $clientTokenInfos, array $userTokenInfos, ApiUser $user)
    {
        parent::__construct($user->getRoles());

        $this->clientTokenInfos = $clientTokenInfos;
        $this->userTokenInfos   = $userTokenInfos;

        $this->setUser($user);
        $this->setAuthenticated(true);
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
     * Returns the user credentials.
     *
     * @return mixed The user credentials
     */
    public function getCredentials()
    {
        return $this->getUser()->getPassword();
    }
}