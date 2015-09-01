<?php

/*
 * This file is part of the VELOCITY package.
 *
 * (c) PHPPRO <opensource@phppro.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Velocity\Bundle\ApiBundle\Traits\SubSubDocument;

use Exception;

/**
 * Create service trait.
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
trait CreateServiceTrait
{
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

        $this->saveCreate($pParentId, $parentId, $array, $options);

        return $this->completeCreate($pParentId, $parentId, $doc, $array, $options);
    }
    /**
     * Create a list of documents.
     *
     * @param string $pParentId
     * @param string $parentId
     * @param mixed  $bulkData
     * @param array  $options
     *
     * @return mixed
     */
    public function createBulk($pParentId, $parentId, $bulkData, $options = [])
    {
        $this->checkBulkData($bulkData, $options);

        $docs   = [];
        $arrays = [];

        foreach ($bulkData as $i => $data) {
            list($docs[$i], $array) = $this->prepareCreate($pParentId, $parentId, $data, $options);
            if (!isset($docs[$i]->id)) {
                throw $this->createException(412, 'Empty id for sub document model');
            }
            $this->pushCreateInBulk($parentId, $arrays, $array);
            unset($bulkData[$i]);
        }

        foreach ($this->saveCreateBulk($pParentId, $parentId, $arrays, $options) as $i => $array) {
            $this->completeCreate($pParentId, $parentId, $docs[$i], $array, $options);
        }

        unset($arrays);

        return $docs;
    }
    /**
     * @param mixed $pParentId
     * @param mixed $parentId
     * @param array $array
     * @param array $options
     *
     * @return mixed
     */
    protected abstract function saveCreate($pParentId, $parentId, array $array, array $options = []);
    /**
     * @param mixed $pParentId
     * @param mixed $parentId
     * @param array $arrays
     * @param array $options
     *
     * @return array
     */
    protected abstract function saveCreateBulk($pParentId, $parentId, array $arrays, array $options = []);
    /**
     * @param mixed $parentId
     * @param array $arrays
     * @param array $array
     *
     * @return mixed
     */
    protected abstract function pushCreateInBulk($parentId, &$arrays, $array);
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
    protected abstract function event($pParentId, $parentId, $event, $data = null);
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
    protected abstract function callback($pParentId, $parentId, $key, $subject = null, $options = []);
    /**
     * @param string $pParentId
     * @param string $parentId
     * @param string $operation
     * @param mixed  $model
     * @param array  $options
     *
     * @return $this
     */
    protected abstract function checkBusinessRules($pParentId, $parentId, $operation, $model, array $options = []);
    /**
     * Test if specified document event has registered event listeners.
     *
     * @param string $event
     *
     * @return bool
     */
    protected abstract function observed($event);
    /**
     * @param mixed $bulkData
     * @param array $options
     *
     * @return $this
     *
     * @throws Exception
     */
    protected abstract function checkBulkData($bulkData, $options = []);
    /**
     * @param string $mode
     * @param array  $data
     * @param array  $options
     *
     * @return mixed
     */
    protected abstract function validateData(array $data = [], $mode = 'create', array $options = []);
    /**
     * @param mixed $model
     * @param array $options
     *
     * @return mixed
     */
    protected abstract function refreshModel($model, array $options = []);
    /**
     * Convert provided model (object) to an array.
     *
     * @param mixed $model
     * @param array $options
     *
     * @return array
     */
    protected abstract function convertToArray($model, array $options = []);
    /**
     * @param string $pParentId
     * @param string $parentId
     * @param array  $data
     * @param array  $options
     *
     * @return array
     */
    protected function prepareCreate($pParentId, $parentId, $data, $options = [])
    {
        $data = $this->callback($pParentId, $parentId, 'create.pre_validate', $data, $options);
        $doc  = $this->validateData($data, 'create', $options);

        unset($data);

        $doc = $this->callback($pParentId, $parentId, 'create.validated', $doc, $options);
        $doc = $this->refreshModel($doc, $options);
        $doc = $this->callback($pParentId, $parentId, 'pre_save', $doc, $options);
        $doc = $this->callback($pParentId, $parentId, 'create.pre_save', $doc, $options);

        $this->checkBusinessRules($pParentId, $parentId, 'create', $doc, $options);

        $doc   = $this->callback($pParentId, $parentId, 'create.pre_save_checked', $doc, $options);
        $array = $this->convertToArray($doc, $options);
        $array = $this->callback($pParentId, $parentId, 'create.pre_save_array', $array, $options);

        return [$doc, $array];
    }
    /**
     * @param string $pParentId
     * @param string $parentId
     * @param mixed  $doc
     * @param mixed  $array
     * @param array  $options
     *
     * @return mixed
     */
    protected function completeCreate($pParentId, $parentId, $doc, $array, $options = [])
    {
        $array = $this->callback($pParentId, $parentId, 'create.saved_array', $array, $options);

        $doc->id = (string) $array['_id'];

        $doc = $this->callback($pParentId, $parentId, 'create.saved', $doc, $options);
        $doc = $this->callback($pParentId, $parentId, 'saved', $doc, $options);
        $doc = $this->callback($pParentId, $parentId, 'created', $doc, $options);

        $this->event($pParentId, $parentId, 'created', $doc);

        return $doc;
    }
    /**
     * @param int    $code
     * @param string $msg
     * @param array  $params
     *
     * @throws Exception
     *
     * @return mixed
     */
    protected abstract function createException($code, $msg, ...$params);
}
