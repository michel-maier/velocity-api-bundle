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
use Velocity\Bundle\ApiBundle\Service\MetaDataServiceAwareInterface;

/**
 * Volatile Sub Document Service.
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
class VolatileSubDocumentService implements MetaDataServiceAwareInterface
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
     * Build the full event name.
     *
     * @param string $event
     *
     * @return string
     */
    protected function buildEventName($event)
    {
        return sprintf('%s.%s.%s', $this->getType(), $this->getSubType(), $event);
    }
    /**
     * Trigger the specified document event if listener are registered.
     *
     * @param mixed  $parentId
     * @param string $event
     * @param mixed  $data
     *
     * @return $this
     */
    protected function event($parentId, $event, $data = null)
    {
        if (!$this->observed($event)) {
            return $this;
        }

        return $this->dispatch($this->buildEventName($event), [($this->getType() . 'Id') => $parentId] + $data);
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
     * @param mixed  $parentId
     * @param string $key
     * @param mixed  $subject
     * @param array  $options
     *
     * @return mixed
     */
    protected function callback($parentId, $key, $subject, $options = [])
    {
        unset($parentId);

        return $this->getMetaDataService()->callback(
            $this->buildEventName($key), $subject, $options
        );
    }
    /**
     * @param mixed $parentId
     * @param array $data
     * @param array $options
     *
     * @return array
     */
    protected function prepareCreate($parentId, $data, $options = [])
    {
        $data  = $this->callback($parentId, 'create.validate.before', $data, $options);
        $doc   = $this->getFormService()->validate(sprintf('%s.%s', $this->getType(), $this->getSubType()), 'create', $data, [], true, $options);
        $doc   = $this->callback($parentId, 'create.validate.after', $doc, $options);
        $doc   = $this->getMetaDataService()->refresh($doc, $options);
        $doc   = $this->callback($parentId, 'save.before', $doc, $options);
        $array = $this->getMetaDataService()->convertObjectToArray($doc, $options + ['removeNulls' => true]);
        $array = $this->callback($parentId, 'create.save.before', $array, $options);

        return [$doc, $array];
    }
    /**
     * @param mixed $parentId
     * @param $doc
     * @param $array
     * @param array $options
     *
     * @return mixed
     */
    protected function completeCreate($parentId, $doc, $array, $options = [])
    {
        $this->callback($parentId, 'create.save.after', $array, $options);

        $doc = $this->callback($parentId, 'save.after', $doc, $options);
        $doc = $this->callback($parentId, 'created', $doc, $options);

        $this->event($parentId, 'created.refresh', $doc);
        $this->event($parentId, 'created', $doc);
        $this->event($parentId, 'created.notify', $doc);

        return $doc;
    }
    /**
     * Create a new document.
     *
     * @param mixed $parentId
     * @param mixed $data
     * @param array $options
     *
     * @return mixed
     */
    public function create($parentId, $data, $options = [])
    {
        list($doc, $array) = $this->prepareCreate($parentId, $data, $options);

        return $this->completeCreate($parentId, $doc, $array, $options);
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
            $this->throwException(412, "Missing bulk data");
        }

        if (!count($bulkData)) {
            $this->throwException(412, "No data to process");
        }

        unset($options);

        return $this;
    }
    /**
     * Create a list of documents.
     *
     * @param mixed $parentId
     * @param mixed $bulkData
     * @param array $options
     *
     * @return mixed
     */
    public function createBulk($parentId, $bulkData, $options = [])
    {
        $this->checkBulkData($bulkData, $options);

        $docs   = [];
        $arrays = [];

        foreach($bulkData as $i => $data) {
            list($doc, $array) = $this->prepareCreate($parentId, $data, $options);
            $docs[$i]   = $doc;
            $arrays[$i] = $array;
        }

        foreach($arrays as $i => $array) {
            unset($arrays[$i]);
            $this->completeCreate($parentId, $docs[$i], $array, $options);
        }

        return $docs;
    }
}