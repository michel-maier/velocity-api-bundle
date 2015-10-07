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
use Velocity\Bundle\ApiBundle\Traits\ModelServiceTrait;

/**
 * Sub Sub Document Service.
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
class SubSubDocumentService implements SubSubDocumentServiceInterface
{
    use ModelServiceTrait;
    use SubSubDocument\HelperTrait;
    use SubSubDocument\GetServiceTrait;
    use SubSubDocument\FindServiceTrait;
    use SubSubDocument\PurgeServiceTrait;
    use SubSubDocument\CreateServiceTrait;
    use SubSubDocument\UpdateServiceTrait;
    use SubSubDocument\DeleteServiceTrait;
    use SubSubDocument\ReplaceServiceTrait;
    use SubSubDocument\TransitionAwareTrait;
    use SubSubDocument\CreateOrUpdateServiceTrait;
    use SubSubDocument\CreateOrDeleteServiceTrait;
    /**
     * @param mixed $pParentId
     * @param mixed $parentId
     * @param array $array
     * @param array $options
     */
    protected function saveCreate($pParentId, $parentId, array $array, array $options = [])
    {
        $this->getRepository()->setHashProperty($pParentId, $this->getRepoKey([$parentId, $array['id']]), $array, $options);
    }
    /**
     * @param mixed $pParentId
     * @param mixed $parentId
     * @param array $arrays
     * @param array $options
     */
    protected function saveCreateBulk($pParentId, $parentId, array $arrays, array $options = [])
    {
        unset($parentId);

        $this->getRepository()->setProperties($pParentId, $arrays, $options);
    }
    /**
     * @param mixed $parentId
     * @param array $arrays
     * @param array $array
     *
     * @return mixed
     */
    protected function pushCreateInBulk($parentId, &$arrays, $array)
    {
        $arrays[$this->mutateKeyToRepoChangesKey('', [$parentId, $array['id']])] = $array;
    }
    /**
     * @param mixed $pParentId
     * @param mixed $parentId
     * @param array $criteria
     * @param array $options
     */
    protected function savePurge($pParentId, $parentId, array $criteria = [], array $options = [])
    {
        unset($criteria);

        $this->getRepository()->resetListProperty($pParentId, $this->getRepoKey([$parentId]), $options);
    }
    /**
     * @param mixed $pParentId
     * @param mixed $parentId
     * @param array $criteria
     * @param array $options
     */
    protected function saveDeleteFound($pParentId, $parentId, array $criteria, array $options)
    {
        unset($criteria);

        $this->getRepository()->resetListProperty($pParentId, $this->getRepoKey([$parentId]), $options);
    }
    /**
     * @param mixed $pParentId
     * @param mixed $parentId
     * @param mixed $id
     * @param array $options
     */
    protected function saveDelete($pParentId, $parentId, $id, array $options)
    {
        $this->getRepository()->unsetProperty($pParentId, $this->getRepoKey([$parentId, $id]), $options);
    }
    /**
     * @param mixed $pParentId
     * @param mixed $parentId
     * @param array $ids
     * @param array $options
     */
    protected function saveDeleteBulk($pParentId, $parentId, $ids, array $options)
    {
        unset($parentId);

        $this->getRepository()->unsetProperty($pParentId, array_values($ids), $options);
    }
    /**
     * @param mixed $pParentId
     * @param mixed $parentId
     * @param mixed $id
     * @param array $array
     * @param array $options
     */
    protected function saveUpdate($pParentId, $parentId, $id, array $array, array $options)
    {
        $this->getRepository()->setProperties($pParentId, $this->mutateArrayToRepoChanges($array, [$parentId, $id]), $options);
    }
    /**
     * @param mixed $pParentId
     * @param mixed $parentId
     * @param array $arrays
     * @param array $options
     */
    protected function saveUpdateBulk($pParentId, $parentId, array $arrays, array $options)
    {
        unset($parentId);

        $this->getRepository()->setProperties($pParentId, $arrays, $options);
    }
    /**
     * @param mixed  $pParentId
     * @param mixed  $parentId
     * @param mixed  $id
     * @param string $property
     * @param mixed  $value
     * @param array  $options
     */
    protected function saveIncrementProperty($pParentId, $parentId, $id, $property, $value, array $options)
    {
        $this->getRepository()->incrementProperty($pParentId, $this->getRepoKey([$parentId, $id, $property]), $value, $options);
    }
    /**
     * @param mixed  $pParentId
     * @param mixed  $parentId
     * @param mixed  $id
     * @param string $property
     * @param mixed  $value
     * @param array  $options
     */
    protected function saveDecrementProperty($pParentId, $parentId, $id, $property, $value, array $options)
    {
        $this->getRepository()->decrementProperty($pParentId, $this->getRepoKey([$parentId, $id, $property]), $value, $options);
    }
    /**
     * @param mixed $parentId
     * @param array $arrays
     * @param mixed $id
     */
    protected function pushDeleteInBulk($parentId, &$arrays, $id)
    {
        $arrays[$id] = $this->getRepoKey([$parentId, $id]);
    }
    /**
     * @param mixed $parentId
     * @param array $arrays
     * @param array $array
     * @param mixed $id
     */
    protected function pushUpdateInBulk($parentId, &$arrays, $array, $id)
    {
        $arrays += $this->mutateArrayToRepoChanges($array, [$parentId, $id]);
    }
    /**
     * Trigger the specified document event if listener are registered.
     *
     * @param string $pParentId
     * @param string $parentId
     * @param string $event
     * @param mixed  $data
     *
     * @return $this
     */
    protected function event($pParentId, $parentId, $event, $data = null)
    {
        return $this->dispatch(
            $this->buildEventName($event),
            new Event\DocumentEvent($data, $this->buildTypeVars([$pParentId, $parentId]))
        );
    }
    /**
     * Execute the registered callback and return the updated subject.
     *
     * @param string $pParentId
     * @param string $parentId
     * @param string $key
     * @param mixed  $subject
     * @param array  $options
     *
     * @return mixed
     */
    protected function callback($pParentId, $parentId, $key, $subject = null, $options = [])
    {
        return $this->getMetaDataService()->callback(
            $this->buildEventName($key),
            $subject,
            $this->buildTypeVars([$pParentId, $parentId]) + $options
        );
    }
    /**
     * @param string $pParentId
     * @param string $parentId
     * @param mixed  $id
     * @param array  $options
     *
     * @return array
     */
    protected function prepareDelete($pParentId, $parentId, $id, $options = [])
    {
        $old = $this->get($pParentId, $parentId, $id, [], $options);

        $this->callback($pParentId, $parentId, 'delete.pre_save', $old, $options);

        $this->applyBusinessRules($pParentId, $parentId, 'delete', $old, $options);

        $this->callback($pParentId, $parentId, 'delete.pre_save_checked', $old, $options);

        return [$old];
    }
    /**
     * @param string $pParentId
     * @param string $parentId
     * @param mixed  $id
     * @param mixed  $old
     * @param array  $options
     *
     * @return mixed
     */
    protected function completeDelete($pParentId, $parentId, $id, $old, $options = [])
    {
        $this->callback($pParentId, $parentId, 'delete.saved', ['id' => $id, 'old' => $old], $options);
        $this->callback($pParentId, $parentId, 'deleted', ['id' => $id, 'old' => $old], $options);

        $this->event($pParentId, $parentId, 'deleted', $id);
        $this->event($pParentId, $parentId, 'deleted_old', $old);

        return $old;
    }
    /**
     * @param string $pParentId
     * @param string $parentId
     * @param string $operation
     * @param mixed  $model
     * @param array  $options
     *
     * @return $this
     */
    protected function applyBusinessRules($pParentId, $parentId, $operation, $model, array $options = [])
    {
        $this->getBusinessRuleService()->executeBusinessRulesForModelOperation(
            $this->getModelName(),
            $operation,
            $model,
            $this->buildTypeVars([$pParentId, $parentId]) + $options
        );

        return $this;
    }
}
