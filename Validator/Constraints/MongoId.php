<?php

namespace Velocity\Bundle\ApiBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class MongoId extends Constraint
{
    public $message = 'Not a valid Mongo ID';
    /**
     * @return string
     */
    public function validatedBy()
    {
        return 'api_mongoId';
    }

}