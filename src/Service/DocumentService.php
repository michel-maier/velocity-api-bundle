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
use Velocity\Bundle\ApiBundle\Traits\ModelServiceTrait;

/**
 * Document Service.
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
class DocumentService implements DocumentServiceInterface
{
    use ModelServiceTrait;
    use Document\HelperTrait;
    use Document\GetServiceTrait;
    use Document\FindServiceTrait;
    use Document\PurgeServiceTrait;
    use Document\CreateServiceTrait;
    use Document\UpdateServiceTrait;
    use Document\DeleteServiceTrait;
    use Document\ReplaceServiceTrait;
    use Document\TransitionAwareTrait;
    use Document\CreateOrUpdateServiceTrait;
    use Document\CreateOrDeleteServiceTrait;
    /**
     * @param array $array
     * @param array $options
     *
     * @return mixed
     */
    protected function saveCreate(array $array, array $options = [])
    {
        $this->getRepository()->create($array, $options);
    }
    /**
     * @param array $arrays
     * @param array $options
     *
     * @return array
     */
    protected function saveCreateBulk(array $arrays, array $options = [])
    {
        return $this->getRepository()->createBulk($arrays, $options);
    }
    /**
     * @param string $id
     * @param array  $array
     * @param array  $options
     *
     * @return mixed
     */
    protected function saveUpdate($id, array $array, array $options)
    {
        $this->getRepository()->update($id, $array, $options);
    }
    /**
     * @param array $arrays
     * @param array $options
     *
     * @return array
     */
    protected function saveUpdateBulk(array $arrays, array $options)
    {
        return $this->getRepository()->updateBulk($arrays, $options);
    }
    /**
     * @param string $id
     * @param string $property
     * @param mixed  $value
     * @param array  $options
     */
    protected function saveIncrementProperty($id, $property, $value, array $options)
    {
        $this->getRepository()->incrementProperty($id, $property, $value, $options);
    }
    /**
     * @param string $id
     * @param string $property
     * @param mixed  $value
     * @param array  $options
     */
    protected function saveDecrementProperty($id, $property, $value, array $options)
    {
        $this->getRepository()->decrementProperty($id, $property, $value, $options);
    }
    /**
     * @param array $criteria
     * @param array $options
     *
     * @return mixed
     */
    protected function savePurge(array $criteria = [], array $options = [])
    {
        $this->getRepository()->deleteFound($criteria, $options);
    }
    /**
     * @param array $criteria
     * @param array $options
     */
    protected function saveDeleteFound(array $criteria, array $options)
    {
        $this->getRepository()->deleteFound($criteria, $options);
    }
    /**
     * @param string $id
     * @param array  $options
     *
     * @return mixed
     */
    protected function saveDelete($id, array $options)
    {
        $this->getRepository()->delete($id, $options);
    }
    /**
     * @param array $ids
     * @param array $options
     *
     * @return array
     */
    protected function saveDeleteBulk($ids, array $options)
    {
        return $this->getRepository()->deleteBulk($ids, $options);
    }
}
