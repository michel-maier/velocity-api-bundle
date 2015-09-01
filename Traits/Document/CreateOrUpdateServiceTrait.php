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

use Exception;

/**
 * Create or update service trait.
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
trait CreateOrUpdateServiceTrait
{
    /**
     * Create document if not exist or update it.
     *
     * @param mixed $data
     * @param array $options
     *
     * @return mixed
     */
    public function createOrUpdate($data, $options = [])
    {
        if (isset($data['id']) && $this->has($data['id'])) {
            $id = $data['id'];
            unset($data['id']);

            return $this->update($id, $data, $options);
        }

        return $this->create($data, $options);
    }
    /**
     * Create documents if not exist or update them.
     *
     * @param mixed $bulkData
     * @param array $options
     *
     * @return mixed
     */
    public function createOrUpdateBulk($bulkData, $options = [])
    {
        $this->checkBulkData($bulkData, $options);

        $toCreate = [];
        $toUpdate = [];

        foreach ($bulkData as $i => $data) {
            unset($bulkData[$i]);
            if (isset($data['id']) && $this->has($data['id'])) {
                $toUpdate[$i] = $data;
            } else {
                $toCreate[$i] = $data;
            }
        }

        unset($bulkData);

        $docs = [];

        if (count($toCreate)) {
            $docs += $this->createBulk($toCreate, $options);
        }

        unset($toCreate);

        if (count($toUpdate)) {
            $docs += $this->updateBulk($toUpdate, $options);
        }

        unset($toUpdate);

        return $docs;
    }
    /**
     * @param array $bulkData
     * @param array $options
     *
     * @return $this
     */
    public abstract function updateBulk($bulkData, $options = []);
    /**
     * Create a list of documents.
     *
     * @param mixed $bulkData
     * @param array $options
     *
     * @return mixed
     */
    public abstract function createBulk($bulkData, $options = []);
    /**
     * Create a new document.
     *
     * @param mixed $data
     * @param array $options
     *
     * @return mixed
     */
    public abstract function create($data, $options = []);
    /**
     * @param string $id
     * @param array  $data
     * @param array  $options
     *
     * @return $this
     */
    public abstract function update($id, $data, $options = []);
    /**
     * Test if specified document exist.
     *
     * @param mixed $id
     * @param array $options
     *
     * @return bool
     */
    public abstract function has($id, $options = []);
    /**
     * @param mixed $bulkData
     * @param array $options
     *
     * @return $this
     *
     * @throws Exception
     */
    protected abstract function checkBulkData($bulkData, $options = []);
}
