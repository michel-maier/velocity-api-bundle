<?php

/*
 * This file is part of the VELOCITY package.
 *
 * (c) PHPPRO <opensource@phppro.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Velocity\Bundle\ApiBundle\Service\Base;

use Exception;
use Velocity\Bundle\ApiBundle\Traits\ServiceTrait;
use Velocity\Bundle\ApiBundle\Traits\LoggerAwareTrait;
use Velocity\Bundle\ApiBundle\Traits\FormServiceAwareTrait;
use Velocity\Bundle\ApiBundle\Traits\MetaDataServiceAwareTrait;

/**
 * Volatile Sub Sub Document Service.
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
class VolatileSubSubDocumentService
{
    use ServiceTrait;
    use LoggerAwareTrait;
    use FormServiceAwareTrait;
    use MetaDataServiceAwareTrait;
    /**
     * @param string $type
     *
     * @return $this
     */
    public function setType($type)
    {
        return $this->setParameter('type', $type);
    }
    /**
     * @return string
     */
    public function getType()
    {
        return $this->getParameter('type');
    }
    /**
     * @param string $subType
     *
     * @return $this
     */
    public function setSubType($subType)
    {
        return $this->setParameter('subType', $subType);
    }
    /**
     * @return string
     */
    public function getSubType()
    {
        return $this->getParameter('subType');
    }
    /**
     * @param string $subSubType
     *
     * @return $this
     */
    public function setSubSubType($subSubType)
    {
        return $this->setParameter('subSubType', $subSubType);
    }
    /**
     * @return string
     */
    public function getSubSubType()
    {
        return $this->getParameter('subSubType');
    }
    /**
     * Build the full event name.
     *
     * @param string $event
     *
     * @return string
     */
    protected function buildEventName($event)
    {
        return sprintf('%s.%s.%s.%s', $this->getType(), $this->getSubType(), $this->getSubSubType(), $event);
    }
    /**
     * Trigger the specified document event if listener are registered.
     *
     * @param mixed  $pParentId
     * @param mixed  $parentId
     * @param string $event
     * @param mixed  $data
     *
     * @return $this
     */
    protected function event($pParentId, $parentId, $event, $data = null)
    {
        if (!$this->observed($event)) {
            return $this;
        }

        return $this->dispatch($this->buildEventName($event), [($this->getType() . 'Id') => $pParentId, ($this->getSubType() . 'Id') => $parentId] + $data);
    }
    /**
     * Test if specified document event has registered event listeners.
     *
     * @param string $event
     *
     * @return bool
     */
    protected function observed($event)
    {
        return $this->hasListeners($this->buildEventName($event));
    }
    /**
     * Execute the registered callback and return the updated subject.
     *
     * @param mixed  $pParentId
     * @param mixed  $parentId
     * @param string $key
     * @param mixed  $subject
     * @param array  $options
     *
     * @return mixed
     */
    protected function callback($pParentId, $parentId, $key, $subject, $options = [])
    {
        unset($pParentId);
        unset($parentId);

        return $this->getMetaDataService()->callback(
            $this->buildEventName($key), $subject, $options
        );
    }
    /**
     * @param mixed $pParentId
     * @param mixed $parentId
     * @param array $data
     * @param array $options
     *
     * @return array
     */
    protected function prepareCreate($pParentId, $parentId, $data, $options = [])
    {
        $data  = $this->callback($pParentId, $parentId, 'create.validate.before', $data, $options);
        $doc   = $this->getFormService()->validate(sprintf('%s.%s.%s', $this->getType(), $this->getSubType(), $this->getSubSubType()), 'create', $data, [], true, $options);
        $doc   = $this->callback($pParentId, $parentId, 'create.validate.after', $doc, $options);
        $doc   = $this->getMetaDataService()->refresh($doc, $options);
        $doc   = $this->callback($pParentId, $parentId, 'save.before', $doc, $options);
        $array = $this->getMetaDataService()->convertObjectToArray($doc, $options + ['removeNulls' => true]);
        $array = $this->callback($pParentId, $parentId, 'create.save.before', $array, $options);

        return [$doc, $array];
    }
    /**
     * @param mixed $pParentId
     * @param mixed $parentId
     * @param $doc
     * @param $array
     * @param array $options
     *
     * @return mixed
     */
    protected function completeCreate($pParentId, $parentId, $doc, $array, $options = [])
    {
        $this->callback($pParentId, $parentId, 'create.save.after', $array, $options);

        $doc = $this->callback($pParentId, $parentId, 'save.after', $doc, $options);
        $doc = $this->callback($pParentId, $parentId, 'created', $doc, $options);

        $this->event($pParentId, $parentId, 'created.refresh', $doc);
        $this->event($pParentId, $parentId, 'created', $doc);
        $this->event($pParentId, $parentId, 'created.notify', $doc);

        return $doc;
    }
    /**
     * Create a new document.
     *
     * @param mixed $pParentId
     * @param mixed $parentId
     * @param mixed $data
     * @param array $options
     *
     * @return mixed
     */
    public function create($pParentId, $parentId, $data, $options = [])
    {
        list($doc, $array) = $this->prepareCreate($pParentId, $parentId, $data, $options);

        return $this->completeCreate($pParentId, $parentId, $doc, $array, $options);
    }
    /**
     * @param mixed $bulkData
     * @param array $options
     *
     * @return $this
     *
     * @throws Exception
     */
    protected function checkBulkData($bulkData, $options = [])
    {
        if (!is_array($bulkData)) {
            throw $this->createException(412, "Missing bulk data");
        }

        if (!count($bulkData)) {
            throw $this->createException(412, "No data to process");
        }

        unset($options);

        return $this;
    }
    /**
     * Create a list of documents.
     *
     * @param mixed $pParentId
     * @param mixed $parentId
     * @param mixed $bulkData
     * @param array $options
     *
     * @return mixed
     */
    public function createBulk($pParentId, $parentId, $bulkData, $options = [])
    {
        $this->checkBulkData($bulkData, $options);

        $docs   = [];
        $arrays = [];

        foreach($bulkData as $i => $data) {
            list($doc, $array) = $this->prepareCreate($pParentId, $parentId, $data, $options);
            $docs[$i]   = $doc;
            $arrays[$i] = $array;
        }

        foreach($arrays as $i => $array) {
            unset($arrays[$i]);
            $this->completeCreate($pParentId, $parentId, $docs[$i], $array, $options);
        }

        return $docs;
    }
}