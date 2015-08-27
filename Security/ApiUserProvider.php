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

use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Velocity\Bundle\ApiBundle\Exception\UnsupportedAccountTypeException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;

/**
 * API User Provider.
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
class ApiUserProvider implements UserProviderInterface
{
    /**
     * @var array
     */
    protected $accountProviders = [];
    /**
     * @param mixed  $accountProvider
     * @param string $type
     * @param string $method
     * @param string $format
     *
     * @return $this
     */
    public function setAccountProvider($accountProvider, $type = 'default', $method = 'get', $format = 'plain')
    {
        $this->accountProviders[$type] = ['method' => $method, 'format' => $format, 'provider' => $accountProvider];

        return $this;
    }
    /**
     * @param string $username
     *
     * @return UserInterface
     *
     * @throws \Exception
     */
    public function loadUserByUsername($username)
    {
        $account = null;

        try {
            $account = $this->getAccount($username);
        } catch (\Exception $e) {
            if (404 === $e->getCode()) {
                throw new UsernameNotFoundException(sprintf('Username "%s" does not exist.', $username));
            }
            throw $e;
        }

        return new ApiUser(is_object($account) ? get_object_vars($account) : $account);
    }
    /**
     * @param string $username
     *
     * @return array
     */
    public function getAccount($username)
    {
        $realUsername = $username;

        $type = 'default';

        if (false !== strpos($username, '/')) {
            list($type, $realUsername) = explode('/', $username, 2);
        }

        $accountProviderDescription = $this->getAccountProviderByType($type);

        $accountProvider = $accountProviderDescription['provider'];
        $method          = $accountProviderDescription['method'];
        $format          = $accountProviderDescription['format'];

        if (!method_exists($accountProvider, $method)) {
            throw new \RuntimeException(
                sprintf("Unable to retrieve account from account provider '%s' (method: %s)", get_class($accountProvider), $method),
                404
            );
        }

        $a = $accountProvider->{$method}($this->unformat($realUsername, $format));

        if (is_object($a)) {
            $a = get_object_vars($a);
        }

        return $a;
    }
    /**
     * @param UserInterface $user
     *
     * @return UserInterface
     *
     * @throws \Exception
     */
    public function refreshUser(UserInterface $user)
    {
        if (!$user instanceof ApiUser) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', get_class($user)));
        }

        /** @var UserInterface $user */

        return $this->loadUserByUsername($user->getUsername());
    }
    /**
     * @param string $class
     *
     * @return bool
     */
    public function supportsClass($class)
    {
        return $class === 'Velocity\\Bundle\\ApiBundle\\Security\\ApiUser';
    }
    /**
     * @param string $value
     * @param string $format
     *
     * @return string
     */
    protected function unformat($value, $format)
    {
        switch ($format) {
            case 'base64':
                return base64_decode($value);
            default:
            case 'plain':
                return $value;
        }
    }
    /**
     * @param string $type
     *
     * @return mixed
     */
    protected function getAccountProviderByType($type)
    {
        if (!isset($this->accountProviders[$type])) {
            throw new UnsupportedAccountTypeException($type);
        }

        return $this->accountProviders[$type];
    }
}
