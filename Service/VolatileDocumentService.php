<?php

/*
 * This file is part of the VELOCITY package.
 *
 * (c) PHPPRO <opensource@phppro.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Velocity\Bundle\ApiBundle\Service;

use Velocity\Bundle\ApiBundle\Event;
use Velocity\Bundle\ApiBundle\Traits\Document;
use Velocity\Bundle\ApiBundle\Traits\VolatileModelServiceTrait;

/**
 * Volatile Document Service.
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
class VolatileDocumentService
{
    use Document\HelperTrait;
    use VolatileModelServiceTrait;
    use Document\CreateServiceTrait;
    /**
     * @param array $array
     * @param array $options
     */
    protected function saveCreate(array $array, array $options = [])
    {
    }
    /**
     * @param array $arrays
     * @param array $options
     *
     * @return array
     */
    protected function saveCreateBulk(array $arrays, array $options = [])
    {
        unset($options);

        return $arrays;
    }
}
