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

use Velocity\Bundle\ApiBundle\Traits\VolatileModelServiceTrait;

/**
 * Volatile Sub Sub Document Service.
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
class VolatileSubSubDocumentService
{
    use VolatileModelServiceTrait;
    /**
     * @return int
     */
    public function getExpectedTypeCount()
    {
        return 3;
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

        unset($data);

        if (!isset($doc->id)) {
            throw $this->createException(412, 'Empty id for sub document model');
        }

        return $this->completeCreate($pParentId, $parentId, $doc, $array, $options);
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

        foreach ($bulkData as $i => $data) {
            list($docs[$i], $arrays[$i]) = $this->prepareCreate($pParentId, $parentId, $data, $options);
            if (!isset($docs[$i]->id)) {
                throw $this->createException(412, 'Empty id for sub document model');
            }
            unset($bulkData[$i]);
        }

        foreach ($arrays as $i => $array) {
            $this->completeCreate($pParentId, $parentId, $docs[$i], $array, $options);
        }

        unset($arrays);

        return $docs;
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
        return $this->dispatch(
            $this->buildEventName($event),
            $this->buildTypeVars([$pParentId, $parentId]) + (is_array($data) ? $data : [])
        );
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
    protected function callback($pParentId, $parentId, $key, $subject = null, $options = [])
    {
        return $this->getMetaDataService()->callback(
            $this->buildEventName($key),
            $subject,
            $this->buildTypeVars([$pParentId, $parentId]) + $options
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
        $data  = $this->callback($pParentId, $parentId, 'create.pre_validate', $data, $options);
        $doc   = $this->validateData($data, 'create', $options);

        unset($data);

        $doc   = $this->callback($pParentId, $parentId, 'create.validated', $doc, $options);
        $doc   = $this->refreshModel($doc, $options);
        $doc   = $this->callback($pParentId, $parentId, 'pre_save', $doc, $options);
        $array = $this->convertToArray($doc, $options);
        $array = $this->callback($pParentId, $parentId, 'create.pre_save', $array, $options);

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
        $array = $this->callback($pParentId, $parentId, 'create.saved', $array, $options);

        $doc->id = (string) $array['_id'];

        $doc = $this->callback($pParentId, $parentId, 'saved', $doc, $options);
        $doc = $this->callback($pParentId, $parentId, 'created', $doc, $options);

        $this->event($pParentId, $parentId, 'created', $doc);

        return $doc;
    }
}
