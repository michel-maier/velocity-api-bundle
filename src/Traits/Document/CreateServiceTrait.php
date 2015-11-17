<?php

/*
 * This file is part of the VELOCITY package.
 *
 * (c) PHPPRO <opensource@phppro.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Velocity\Bundle\ApiBundle\Traits\Document;

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
     * @param mixed $data
     * @param array $options
     *
     * @return mixed
     */
    public function create($data, $options = [])
    {
        list($doc, $array) = $this->prepareCreate($data, $options);

        unset($data);

        $this->saveCreate($array, $options);

        return $this->completeCreate($doc, $array, $options);
    }
    /**
     * Create a list of documents.
     *
     * @param mixed $bulkData
     * @param array $options
     *
     * @return mixed
     */
    public function createBulk($bulkData, $options = [])
    {
        $this->checkBulkData($bulkData, $options);

        $docs   = [];
        $arrays = [];

        foreach ($bulkData as $i => $data) {
            list($docs[$i], $arrays[$i]) = $this->prepareCreate($data, $options);
            unset($bulkData[$i]);
        }

        foreach ($this->saveCreateBulk($arrays, $options) as $i => $array) {
            unset($arrays[$i]);
            $this->completeCreate($docs[$i], $array, $options);
        }

        return $docs;
    }
    /**
     * @param array $array
     * @param array $options
     *
     * @return mixed
     */
    protected abstract function saveCreate(array $array, array $options = []);
    /**
     * @param array $arrays
     * @param array $options
     *
     * @return array
     */
    protected abstract function saveCreateBulk(array $arrays, array $options = []);
    /**
     * Trigger the specified document event if listener are registered.
     *
     * @param string $event
     * @param mixed  $data
     *
     * @return $this
     */
    protected abstract function event($event, $data = null);
    /**
     * Execute the registered callback and return the updated subject.
     *
     * @param string $key
     * @param mixed  $subject
     * @param array  $options
     *
     * @return mixed
     */
    protected abstract function callback($key, $subject = null, $options = []);
    /**
     * @param string $operation
     * @param mixed  $model
     * @param array  $options
     *
     * @return $this
     */
    protected abstract function applyBusinessRules($operation, $model, array $options = []);
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
     * @throws \Exception
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
     * @param mixed $model
     * @param array $options
     *
     * @return mixed
     */
    protected abstract function cleanModel($model, array $options = []);
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
     * @param array $data
     * @param array $options
     *
     * @return array
     */
    protected function prepareCreate($data, $options = [])
    {
        $data = $this->callback('create.pre_validate', $data, $options);
        $doc  = $this->validateData($data, 'create', $options);

        unset($data);

        $doc = $this->callback('create.validated', $doc, $options);
        $doc = $this->refreshModel($doc, ['operation' => 'create'] + $options);
        $doc = $this->callback('pre_save', $doc, $options);
        $doc = $this->callback('create.pre_save', $doc, $options);

        $this->applyBusinessRules('create', $doc, $options);

        $doc   = $this->callback('create.pre_save_checked', $doc, $options);
        $array = $this->convertToArray($doc, $options);
        $array = $this->callback('create.pre_save_array', $array, $options);

        return [$doc, $array];
    }
    /**
     * @param mixed $doc
     * @param array $array
     * @param array $options
     *
     * @return mixed
     */
    protected function completeCreate($doc, $array, $options = [])
    {
        $array = $this->callback('create.saved_array', $array, $options);

        $doc->id = (string) $array['_id'];

        $doc = $this->cleanModel($doc, ['operation' => 'create'] + $options);

        $doc = $this->callback('create.saved', $doc, $options);
        $doc = $this->callback('saved', $doc, $options);
        $doc = $this->callback('created', $doc, $options);

        $this->applyBusinessRules('complete_create', $doc, $options);

        $this->event('created', $doc);

        return $doc;
    }
}
