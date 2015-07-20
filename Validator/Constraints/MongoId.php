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

/**
 * MongoId constraint.
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 *
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