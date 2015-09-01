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
use Velocity\Bundle\ApiBundle\Traits\SubDocument;
use Velocity\Bundle\ApiBundle\Traits\VolatileModelServiceTrait;

/**
 * Volatile Sub Document Service.
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
class VolatileSubDocumentService
{
    use SubDocument\HelperTrait;
    use VolatileModelServiceTrait;
    use SubDocument\CreateServiceTrait;
    /**
     * @param mixed $parentId
     * @param array $array
     * @param array $options
     */
    protected function saveCreate($parentId, array $array, array $options = [])
    {
    }
    /**
     * @param mixed $parentId
     * @param array $arrays
     * @param array $options
     *
     * @return array
     */
    protected function saveCreateBulk($parentId, array $arrays, array $options = [])
    {
        unset($parentId, $options);

        return $arrays;
    }
    /**
     * @param array $arrays
     * @param array $array
     */
    protected function pushCreateInBulk(&$arrays, $array)
    {
        $arrays[] = $array;
    }
}
