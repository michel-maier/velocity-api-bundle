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
use Velocity\Bundle\ApiBundle\Traits\SubSubDocument;
use Velocity\Bundle\ApiBundle\Traits\VolatileModelServiceTrait;

/**
 * Volatile Sub Sub Document Service.
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
class VolatileSubSubDocumentService
{
    use VolatileModelServiceTrait;
    use SubSubDocument\HelperTrait;
    use SubSubDocument\CreateServiceTrait;
    /**
     * @param mixed $pParentId
     * @param mixed $parentId
     * @param array $array
     * @param array $options
     */
    protected function saveCreate($pParentId, $parentId, array $array, array $options = [])
    {
    }
    /**
     * @param mixed $pParentId
     * @param mixed $parentId
     * @param array $arrays
     * @param array $options
     *
     * @return array
     */
    protected function saveCreateBulk($pParentId, $parentId, array $arrays, array $options = [])
    {
        unset($pParentId, $parentId, $options);

        return $arrays;
    }
    /**
     * @param mixed $parentId
     * @param array $arrays
     * @param array $array
     */
    protected function pushCreateInBulk($parentId, &$arrays, $array)
    {
        unset($parentId);

        $arrays[] = $array;
    }
}
