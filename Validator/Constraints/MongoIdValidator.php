<?php

/*
 * This file is part of the VELOCITY package.
 *
 * (c) PHPPRO <opensource@phppro.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Velocity\Bundle\ApiBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * MongoId Validator.
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
class MongoIdValidator extends ConstraintValidator
{
    /**
     * @param mixed      $value
     * @param Constraint $constraint
     */
    public function validate($value, Constraint $constraint)
    {
        if (!preg_match('/^[0-9a-f]{24}$/', $value, $matches)) {
            $this->context->addViolation($constraint->message, ['%value%' => $value]);
        }
    }
}
