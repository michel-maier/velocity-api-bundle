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
use Velocity\Bundle\ApiBundle\Traits\VolatileModelServiceTrait;

/**
 * Volatile Document Service.
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
class VolatileDocumentService
{
    use VolatileModelServiceTrait;
    /**
     * @return int
     */
    public function getExpectedTypeCount()
    {
        return 1;
    }
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

        foreach ($arrays as $i => $array) {
            unset($arrays[$i]);
            $this->completeCreate($docs[$i], $array, $options);
        }

        return $docs;
    }
    /**
     * Trigger the specified document event if listener are registered.
     *
     * @param string $event
     * @param mixed  $data
     *
     * @return $this
     */
    protected function event($event, $data = null)
    {
        return $this->dispatch($this->buildEventName($event), new Event\DocumentEvent($data));
    }
    /**
     * Execute the registered callback and return the updated subject.
     *
     * @param string $key
     * @param mixed  $subject
     * @param array  $options
     *
     * @return mixed
     */
    protected function callback($key, $subject = null, $options = [])
    {
        return $this->getMetaDataService()->callback($this->buildEventName($key), $subject, $options);
    }
    /**
     * @param array $data
     * @param array $options
     *
     * @return array
     */
    protected function prepareCreate($data, $options = [])
    {
        $data  = $this->callback('create.pre_validate', $data, $options);
        $doc   = $this->validateData($data, 'create', $options);

        unset($data);

        $doc   = $this->callback('create.validated', $doc, $options);
        $doc   = $this->refreshModel($doc, $options);
        $doc   = $this->callback('pre_save', $doc, $options);
        $array = $this->convertToArray($doc, $options);
        $array = $this->callback('create.pre_save', $array, $options);

        return [$doc, $array];
    }
    /**
     * @param $doc
     * @param $array
     * @param array $options
     *
     * @return mixed
     */
    protected function completeCreate($doc, $array, $options = [])
    {
        $array = $this->callback('create.saved', $array, $options);

        $doc->id = (string) $array['_id'];

        $doc = $this->callback('saved', $doc, $options);
        $doc = $this->callback('created', $doc, $options);

        $this->event('created', $doc);

        return $doc;
    }
}
