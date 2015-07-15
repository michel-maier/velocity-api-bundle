<?php

/*
 * This file is part of the VELOCITY package.
 *
 * (c) PHPPRO <opensource@phppro.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Velocity\Bundle\ApiBundle\Security\Voter;

use Symfony\Component\HttpFoundation\Request;
use Velocity\Bundle\ApiBundle\Security\ApiUser;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

/**
 * Abstract User Voter.
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
abstract class AbstractUserVoter implements VoterInterface
{
    /**
     * Checks if the voter supports the given attribute.
     *
     * @param string $attribute An attribute
     *
     * @return bool    true if this Voter supports the attribute, false otherwise
     *
     * @override
     */
    public function supportsAttribute($attribute)
    {
        return true;
    }
    /**
     * Checks if the voter supports the given class.
     *
     * @param string $class A class name
     *
     * @return bool    true if this Voter can process the class
     */
    public function supportsClass($class)
    {
        return true;
    }
    /**
     * Returns the vote for the given parameters.
     *
     * This method must return one of the following constants:
     * ACCESS_GRANTED, ACCESS_DENIED, or ACCESS_ABSTAIN.
     *
     * @param TokenInterface $token A TokenInterface instance
     * @param object|null $object The object to secure
     * @param array $attributes An array of attributes associated with the method being invoked
     *
     * @return int     either ACCESS_GRANTED, ACCESS_ABSTAIN, or ACCESS_DENIED
     */
    public function vote(TokenInterface $token, $object, array $attributes)
    {
        $result = self::ACCESS_ABSTAIN;

        if (!($object instanceof Request)) {
            return $result;
        }

        $user = $token->getUser();

        if (!($user instanceof ApiUser)) {
            return $result;
        }

        /* @var $object Request */

        $result = self::ACCESS_DENIED;

        foreach ($attributes as $attribute) {
            if (!$this->supportsAttribute($attribute)) {
                continue;
            }

            if ($this->isAllowed($user, $attribute, $object)) {
                $result = self::ACCESS_GRANTED;
                break;
            }
        }

        return $result;
    }
    /**
     * @param ApiUser $user
     * @param string  $attribute
     * @param Request $request
     *
     * @return bool
     */
    public function isAllowed(ApiUser $user, $attribute, Request $request)
    {
        unset($attribute);
        unset($request);
        unset($user);

        return true;
    }
}