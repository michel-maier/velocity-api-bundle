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
use Velocity\Bundle\ApiBundle\Traits\ModelServiceTrait;

/**
 * Sub Document Service.
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
class SubDocumentService implements SubDocumentServiceInterface
{
    use ModelServiceTrait;
    use SubDocument\HelperTrait;
    use SubDocument\GetServiceTrait;
    use SubDocument\FindServiceTrait;
    use SubDocument\PurgeServiceTrait;
    use SubDocument\CreateServiceTrait;
    use SubDocument\UpdateServiceTrait;
    use SubDocument\DeleteServiceTrait;
    use SubDocument\ReplaceServiceTrait;
    use SubDocument\CreateByServiceTrait;
    use SubDocument\TransitionAwareTrait;
    use SubDocument\CreateOrUpdateServiceTrait;
    use SubDocument\CreateOrDeleteServiceTrait;
    /**
     * Returns the parent id based on the specified field and value to select it.
     *
     * @param string $field
     * @param mixed  $value
     *
     * @return string
     */
    protected function getParentIdBy($field, $value)
    {
        return (string) $this->getRepository()->get([$field => $value], ['_id'])['_id'];
    }
    /**
     * @param mixed $parentId
     * @param array $array
     * @param array $options
     */
    protected function saveCreate($parentId, array $array, array $options = [])
    {
        $this->getRepository()->setHashProperty($parentId, $this->getRepoKey([$array['_id']]), $array, $options);
    }
    /**
     * @param mixed $parentId
     * @param array $arrays
     * @param array $options
     */
    protected function saveCreateBulk($parentId, array $arrays, array $options = [])
    {
        $this->getRepository()->setProperties($parentId, $arrays, $options);
    }
    /**
     * @param array $arrays
     * @param array $array
     *
     * @return mixed
     */
    protected function pushCreateInBulk(&$arrays, $array)
    {
        $arrays[$this->mutateKeyToRepoChangesKey('', [$array['_id']])] = $array;
    }
    /**
     * @param mixed $parentId
     * @param array $criteria
     * @param array $options
     */
    protected function savePurge($parentId, array $criteria = [], array $options = [])
    {
        unset($criteria);

        $this->getRepository()->resetListProperty($parentId, $this->getRepoKey(), $options);
    }
    /**
     * @param mixed $parentId
     * @param array $criteria
     * @param array $options
     */
    protected function saveDeleteFound($parentId, array $criteria, array $options)
    {
        unset($criteria);

        $this->getRepository()->resetListProperty($parentId, $this->getRepoKey(), $options);
    }
    /**
     * @param mixed $parentId
     * @param mixed $id
     * @param array $options
     */
    protected function saveDelete($parentId, $id, array $options)
    {
        $this->getRepository()->unsetProperty($parentId, $this->getRepoKey([$id]), $options);
    }
    /**
     * @param mixed $parentId
     * @param array $ids
     * @param array $options
     */
    protected function saveDeleteBulk($parentId, $ids, array $options)
    {
        $this->getRepository()->unsetProperty($parentId, array_values($ids), $options);
    }
    /**
     * @param mixed $parentId
     * @param mixed $id
     * @param array $array
     * @param array $options
     */
    protected function saveUpdate($parentId, $id, array $array, array $options)
    {
        $this->getRepository()->setProperties($parentId, $this->mutateArrayToRepoChanges($array, [$id]), $options);
    }
    /**
     * @param mixed $parentId
     * @param array $arrays
     * @param array $options
     */
    protected function saveUpdateBulk($parentId, array $arrays, array $options)
    {
        $this->getRepository()->setProperties($parentId, $arrays, $options);
    }
    /**
     * @param mixed  $parentId
     * @param mixed  $id
     * @param string $property
     * @param mixed  $value
     * @param array  $options
     */
    protected function saveIncrementProperty($parentId, $id, $property, $value, array $options)
    {
        $this->getRepository()->incrementProperty($parentId, $this->getRepoKey([$id, $property]), $value, $options);
    }
    /**
     * @param mixed  $parentId
     * @param mixed  $id
     * @param string $property
     * @param mixed  $value
     * @param array  $options
     */
    protected function saveDecrementProperty($parentId, $id, $property, $value, array $options)
    {
        $this->getRepository()->decrementProperty($parentId, $this->getRepoKey([$id, $property]), $value, $options);
    }
    /**
     * @param array $arrays
     * @param mixed $id
     */
    protected function pushDeleteInBulk(&$arrays, $id)
    {
        $arrays[$id] = $this->getRepoKey([$id]);
    }
    /**
     * @param array $arrays
     * @param array $array
     * @param mixed $id
     */
    protected function pushUpdateInBulk(&$arrays, $array, $id)
    {
        $arrays += $this->mutateArrayToRepoChanges($array, [$id]);
    }
    /**
     * Trigger the specified document event if listener are registered.
     *
     * @param string $parentId
     * @param string $event
     * @param mixed  $data
     *
     * @return $this
     */
    protected function event($parentId, $event, $data = null)
    {
        return $this->dispatch(
            $this->buildEventName($event),
            new Event\DocumentEvent($data, $this->buildTypeVars([$parentId]))
        );
    }
    /**
     * @param string $parentId
     * @param string $operation
     * @param mixed  $model
     * @param array  $options
     *
     * @return $this
     */
    protected function applyBusinessRules($parentId, $operation, $model, array $options = [])
    {
        $this->getBusinessRuleService()->executeBusinessRulesForModelOperation(
            $this->getModelName(),
            $operation,
            $model,
            $this->buildTypeVars([$parentId]) + $options
        );

        return $this;
    }
}
