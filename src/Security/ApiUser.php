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

use DateTime;
use JMS\Serializer\Annotation as Jms;
use Symfony\Component\Security\Core\Role\Role;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;

/**
 * API User.
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 *
 * @Jms\ExclusionPolicy("all")
 * @Jms\AccessorOrder("alphabetical")
 */
class ApiUser implements AdvancedUserInterface
{
    /**
     * @var bool
     *
     * @Jms\Expose
     * @Jms\Groups({"detailed"})
     * @Jms\Type("boolean")
     */
    protected $expired;
    /**
     * @var bool
     *
     * @Jms\Expose
     * @Jms\Groups({"detailed"})
     * @Jms\Type("boolean")
     */
    protected $locked;
    /**
     * @var string
     *
     * @Jms\Expose
     * @Jms\Groups({"detailed"})
     * @Jms\Type("string")
     */
    protected $firstName;
    /**
     * @var string
     *
     * @Jms\Expose
     * @Jms\Groups({"detailed"})
     * @Jms\Type("string")
     */
    protected $lastName;
    /**
     * @var string
     *
     * @Jms\Expose
     * @Jms\Groups({"detailed"})
     * @Jms\Type("string")
     */
    protected $email;
    /**
     * @var array
     *
     * @Jms\Expose
     * @Jms\Groups({"detailed"})
     * @Jms\Type("array<string>")
     * @Jms\Accessor(getter="getFlattenRoles")
     */
    protected $roles;
    /**
     * @var DateTime
     *
     * @Jms\Expose
     * @Jms\Groups({"detailed"})
     * @Jms\Type("DateTime<'c'>")
     */
    protected $createDate;
    /**
     * @var DateTime
     *
     * @Jms\Expose
     * @Jms\Groups({"detailed"})
     * @Jms\Type("DateTime<'c'>")
     */
    protected $updateDate;
    /**
     * @var bool
     *
     * @Jms\Expose
     * @Jms\Groups({"detailed"})
     * @Jms\Type("boolean")
     */
    protected $enabled;
    /**
     * @var string
     *
     * @Jms\Expose
     * @Jms\Groups({"detailed"})
     * @Jms\Type("string")
     */
    protected $password;
    /**
     * @var DateTime
     *
     * @Jms\Expose
     * @Jms\Groups({"detailed"})
     * @Jms\Type("DateTime<'c'>")
     */
    protected $disableDate;
    /**
     * @var DateTime
     *
     * @Jms\Expose
     * @Jms\Groups({"detailed"})
     * @Jms\Type("DateTime<'c'>")
     */
    protected $expireDate;
    /**
     * @var DateTime
     *
     * @Jms\Expose
     * @Jms\Groups({"detailed"})
     * @Jms\Type("DateTime<'c'>")
     */
    protected $lockDate;
    /**
     * @var string
     *
     * @Jms\Expose
     * @Jms\Groups({"detailed"})
     * @Jms\Type("string")
     */
    protected $token;
    /**
     * @var string
     *
     * @Jms\Expose
     * @Jms\Groups({"detailed"})
     * @Jms\Type("string")
     */
    protected $id;
    /**
     * @var array
     *
     * @Jms\Expose
     * @Jms\Groups({"detailed"})
     * @Jms\Type("array")
     */
    protected $attributes;
    /**
     * @var string
     *
     * @Jms\Expose
     * @Jms\Groups({"detailed"})
     * @Jms\Type("string")
     */
    protected $salt;
    /**
     * @var bool
     *
     * @Jms\Expose
     * @Jms\Groups({"detailed"})
     * @Jms\Type("boolean")
     */
    protected $admin;
    /**
     * @var bool
     *
     * @Jms\Expose
     * @Jms\Groups({"detailed"})
     * @Jms\Type("boolean")
     */
    protected $allowedToSwitch;
    /**
     * @var string
     *
     * @Jms\Expose
     * @Jms\Groups({"detailed"})
     * @Jms\Type("string")
     */
    protected $username;
    /**
     * @var string
     *
     * @Jms\Expose
     * @Jms\Groups({"detailed"})
     * @Jms\Type("string")
     */
    protected $name;
    /**
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->expired         = false;
        $this->locked          = false;
        $this->enabled         = true;
        $this->firstName       = null;
        $this->lastName        = null;
        $this->email           = null;
        $this->salt            = null;
        $this->roles           = ['ROLE_USER'];
        $this->createDate      = null;
        $this->updateDate      = null;
        $this->disableDate     = null;
        $this->expireDate      = null;
        $this->lockDate        = null;
        $this->password        = null;
        $this->token           = null;
        $this->id              = null;
        $this->admin           = false;
        $this->allowedToSwitch = false;

        if (isset($data['username'])) {
            $this->username = $data['username'];
        } else {
            $this->username = $data['id'];
        }
        if (isset($data['expired'])) {
            $this->expired = (bool) $data['expired'];
        }
        if (isset($data['enabled'])) {
            $this->enabled = (bool) $data['enabled'];
        }
        if (isset($data['locked'])) {
            $this->locked  = (bool) $data['locked'];
        }
        if (isset($data['firstName'])) {
            $this->firstName = (string) $data['firstName'];
        }
        if (isset($data['lastName'])) {
            $this->lastName = (string) $data['lastName'];
        }
        if (isset($data['name'])) {
            $this->name = $data['name'];
        } else {
            $this->name = sprintf('%s %s', ucfirst($this->firstName), ucfirst($this->lastName));
        }
        if (isset($data['email'])) {
            $this->email = (string) $data['email'];
        }
        if (isset($data['salt'])) {
            $this->salt = (string) $data['salt'];
        }
        if (isset($data['roles'])) {
            $data['roles']['user'] = true;
            $this->roles = array_map(
                function ($v) {
                    return 'ROLE_'.strtoupper(str_replace('.', '_', $v));
                },
                array_keys($data['roles'])
            );
            if (isset($data['roles']['admin']) && true === $data['roles']['admin']) {
                $this->admin = true;
            }
            if (true === $this->admin || (isset($data['roles']['allowed.to.switch']) && true === $data['roles']['allowed.to.switch'])) {
                $this->allowedToSwitch = true;
            }
            sort($this->roles);
        }
        if (isset($data['createDate'])) {
            $this->createDate = $data['createDate'];
        }
        if (isset($data['updateDate'])) {
            $this->updateDate = $data['updateDate'];
        }
        if (isset($data['disableDate'])) {
            $this->disableDate = $data['disableDate'];
        }
        if (isset($data['expireDate'])) {
            $this->expireDate = $data['expireDate'];
        }
        if (isset($data['lockDate'])) {
            $this->lockDate = $data['lockDate'];
        }
        if (isset($data['password'])) {
            $this->password = (string) $data['password'];
        }
        if (isset($data['token'])) {
            $this->token = (string) $data['token'];
        }
        if (isset($data['id'])) {
            $this->id = (string) $data['id'];
        }

        unset($data['email']);
        unset($data['lastName']);
        unset($data['expired']);
        unset($data['enabled']);
        unset($data['locked']);
        unset($data['firstName']);
        unset($data['salt']);
        unset($data['roles']);
        unset($data['createDate']);
        unset($data['updateDate']);
        unset($data['disableDate']);
        unset($data['expireDate']);
        unset($data['lockDate']);
        unset($data['password']);
        unset($data['token']);
        unset($data['id']);
        unset($data['username']);
        unset($data['name']);

        $this->attributes = $data;
    }
    /**
     * Checks whether the user's account has expired.
     *
     * Internally, if this method returns false, the authentication system
     * will throw an AccountExpiredException and prevent login.
     *
     * @return bool true if the user's account is non expired, false otherwise
     *
     * @see AccountExpiredException
     */
    public function isAccountNonExpired()
    {
        return !$this->expired;
    }
    /**
     * Checks whether the user is locked.
     *
     * Internally, if this method returns false, the authentication system
     * will throw a LockedException and prevent login.
     *
     * @return bool true if the user is not locked, false otherwise
     *
     * @see LockedException
     */
    public function isAccountNonLocked()
    {
        return !$this->locked;
    }
    /**
     * Checks whether the user's credentials (password) has expired.
     *
     * Internally, if this method returns false, the authentication system
     * will throw a CredentialsExpiredException and prevent login.
     *
     * @return bool true if the user's credentials are non expired, false otherwise
     *
     * @see CredentialsExpiredException
     */
    public function isCredentialsNonExpired()
    {
        return true;
    }
    /**
     * Checks whether the user is enabled.
     *
     * Internally, if this method returns false, the authentication system
     * will throw a DisabledException and prevent login.
     *
     * @return bool true if the user is enabled, false otherwise
     *
     * @see DisabledException
     */
    public function isEnabled()
    {
        return $this->enabled;
    }
    /**
     * Returns the roles granted to the user.
     *
     * <code>
     * public function getRoles()
     * {
     *     return array('ROLE_USER');
     * }
     * </code>
     *
     * Alternatively, the roles might be stored on a ``roles`` property,
     * and populated in any number of different ways when the user object
     * is created.
     *
     * @return Role[] The user roles
     */
    public function getRoles()
    {
        return $this->roles;
    }
    /**
     * Returns the password used to authenticate the user.
     *
     * This should be the encoded password. On authentication, a plain-text
     * password will be salted, encoded, and then compared to this value.
     *
     * @return string The password
     */
    public function getPassword()
    {
        return $this->password;
    }
    /**
     * Returns the salt that was originally used to encode the password.
     *
     * This can return null if the password was not encoded using a salt.
     *
     * @return string|null The salt
     */
    public function getSalt()
    {
        return $this->salt;
    }
    /**
     * Returns the username used to authenticate the user.
     *
     * @return string The username
     */
    public function getUsername()
    {
        return $this->username;
    }
    /**
     * Removes sensitive data from the user.
     *
     * This is important if, at any given point, sensitive information like
     * the plain-text password is stored on this object.
     */
    public function eraseCredentials()
    {
    }
    /**
     * @return array
     */
    public function getAttributes()
    {
        return $this->attributes;
    }
    /**
     * @param string $key
     * @param mixed  $default
     *
     * @return mixed
     */
    public function getAttribute($key, $default = null)
    {
        return isset($this->attributes[$key]) ? $this->attributes[$key] : $default;
    }
    /**
     * @return array
     */
    public function getFlattenRoles()
    {
        return array_map(
            function ($v) {
                return strtolower(str_replace('_', '.', preg_replace('/^ROLE_/', '', $v)));
            },
            $this->getRoles()
        );
    }
    /**
     * @return bool
     */
    public function isAdmin()
    {
        return true === $this->admin;
    }
    /**
     * @return bool
     */
    public function isAllowedToswitch()
    {
        return true === $this->allowedToSwitch;
    }
}
