<?php

namespace Velocity\Bundle\ApiBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

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