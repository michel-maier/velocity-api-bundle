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
 * Volatile Sub Document Service.
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
class VolatileSubDocumentService
{
    use VolatileModelServiceTrait;
    /**
     * @return int
     */
    public function getExpectedTypeCount()
    {
        return 2;
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

        unset($data);

        if (!isset($doc->id)) {
            throw $this->createException(412, 'Empty id for sub document model');
        }

        return $this->completeCreate($parentId, $doc, $array, $options);
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

        foreach ($bulkData as $i => $data) {
            list($docs[$i], $arrays[$i]) = $this->prepareCreate($parentId, $data, $options);
            if (!isset($docs[$i]->id)) {
                throw $this->createException(412, 'Empty id for sub document model');
            }
            unset($bulkData[$i]);
        }

        foreach ($arrays as $i => $array) {
            $this->completeCreate($parentId, $docs[$i], $array, $options);
        }

        unset($arrays);

        return $docs;
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
        return $this->dispatch(
            $this->buildEventName($event),
            $this->buildTypeVars([$parentId]) + (is_array($data) ? $data : [])
        );
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
    protected function callback($parentId, $key, $subject = null, $options = [])
    {
        return $this->getMetaDataService()->callback(
            $this->buildEventName($key),
            $subject,
            $this->buildTypeVars([$parentId]) + $options
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
        $data  = $this->callback($parentId, 'create.pre_validate', $data, $options);
        $doc   = $this->validateData($data, 'create', $options);

        unset($data);

        $doc   = $this->callback($parentId, 'create.validated', $doc, $options);
        $doc   = $this->refreshModel($doc, $options);
        $doc   = $this->callback($parentId, 'pre_save', $doc, $options);
        $array = $this->convertToArray($doc, $options);
        $array = $this->callback($parentId, 'create.pre_save', $array, $options);

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
        $array = $this->callback($parentId, 'create.saved', $array, $options);

        $doc->id = (string) $array['_id'];

        $doc = $this->callback($parentId, 'saved', $doc, $options);
        $doc = $this->callback($parentId, 'created', $doc, $options);

        $this->event($parentId, 'created', $doc);

        return $doc;
    }
}
