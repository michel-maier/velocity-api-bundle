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

use Velocity\Bundle\ApiBundle\Traits\ServiceTrait;
use Velocity\Bundle\ApiBundle\Service\ClientServiceInterface;
use Velocity\Bundle\ApiBundle\Traits\RequestServiceAwareTrait;
use Velocity\Bundle\ApiBundle\Exception\BadUserTokenException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Velocity\Bundle\ApiBundle\Exception\BadClientTokenException;
use Velocity\Bundle\ApiBundle\Exception\MissingUserIdentityException;
use Velocity\Bundle\ApiBundle\Exception\MissingSudoPrivilegeException;
use Velocity\Bundle\ApiBundle\Exception\MissingClientIdentityException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface;

/**
 * API Authentication Provider.
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
class ApiAuthenticationProvider implements AuthenticationProviderInterface
{
    use ServiceTrait;
    use RequestServiceAwareTrait;
    /**
     * @param UserProviderInterface  $userProvider
     */
    public function __construct(UserProviderInterface $userProvider)
    {
        $this->setUserProvider($userProvider);
    }
    /**
     * @param ClientServiceInterface $clientService
     *
     * @return $this
     */
    public function setClientService(ClientServiceInterface $clientService)
    {
        return $this->setService('client', $clientService);
    }
    /**
     * @return ClientServiceInterface
     */
    public function getClientService()
    {
        return $this->getService('client');
    }
    /**
     * @param UserProviderInterface $clientService
     *
     * @return $this
     */
    public function setUserProvider(UserProviderInterface $clientService)
    {
        return $this->setService('userProvider', $clientService);
    }
    /**
     * @return UserProviderInterface
     */
    public function getUserProvider()
    {
        return $this->getService('userProvider');
    }
    /**
     * @param TokenInterface $token
     *
     * @return TokenInterface
     */
    public function authenticate(TokenInterface $token)
    {
        $now = new \DateTime();

        /** @var ApiUnauthenticatedUserToken $token */
        $clientTokenInfos = $this->validateClientToken($token->getClientTokenInfos(), $now);
        $userTokenInfos   = $this->validateUserToken($token->getUserTokenInfos(), $now);

        $username = $token->getUsername();

        if ($token->isImpersonating()) {
            /** @var ApiUser $sudoer */
            $sudoer = $this->getUserProvider()->loadUserByUsername($token->getUsername());

            if (!$sudoer->isAllowedToSwitch()) {
                throw new MissingSudoPrivilegeException;
            }

            $username = $token->getImpersonatedUserInfos()['id'];
        }

        return new ApiAuthenticatedUserToken(
            $clientTokenInfos,
            $userTokenInfos,
            $this->getUserProvider()->loadUserByUsername($username)
        );
    }
    /**
     * @param array     $infos
     * @param \DateTime $now
     *
     * @return array
     *
     * @throws \Exception
     */
    protected function validateClientToken($infos, \DateTime $now)
    {
        if (!isset($infos['id'])) throw new MissingClientIdentityException;

        if (false === (
                $this->getRequestService()->buildClientToken($infos['id'], $infos['expire'], $this->getRequestService()->getClientSecret()) === $infos['token']
                && false === $this->getRequestService()->isDateExpired($now, $this->getRequestService()->convertStringToDateTime($infos['expire']))
            )) {
            throw new BadClientTokenException;
        }

        if (isset($infos['id'])) {
            return array_merge($infos, $this->getClientService()->get($infos['id'], [], ['model' => false]));
        }

        return $infos;
    }
    /**
     * @param array     $infos
     * @param \DateTime $now
     *
     * @return array
     */
    protected function validateUserToken($infos, \DateTime $now)
    {
        if (!isset($infos['id'])) throw new MissingUserIdentityException;

        if (false === (
                $this->getRequestService()->buildUserToken($infos['id'], $infos['expire'], $this->getRequestService()->getUserSecret()) === $infos['token']
                && false === $this->getRequestService()->isDateExpired($now, $this->getRequestService()->convertStringToDateTime($infos['expire']))
            )) {
            throw new BadUserTokenException;
        }


        /**
        if (isset($infos['id'])) return array_merge($this->getUser($infos['id']), $infos, ['id' => $infos['id']]);
        */
        return $infos;
    }
    /**
     * @param TokenInterface $token
     *
     * @return bool
     */
    public function supports(TokenInterface $token)
    {
        return $token instanceof ApiUnauthenticatedUserToken;
    }
}