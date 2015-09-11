<?php

/*
 * This file is part of the VELOCITY package.
 *
 * (c) PHPPRO <opensource@phppro.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Velocity\Bundle\ApiBundle\Traits\SubDocument;

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
     * @param string $parentId
     * @param mixed  $data
     * @param array  $options
     *
     * @return mixed
     */
    public function createOrUpdate($parentId, $data, $options = [])
    {
        if (isset($data['id']) && $this->has($parentId, $data['id'])) {
            $id = $data['id'];
            unset($data['id']);

            return $this->update($parentId, $id, $data, $options);
        }

        return $this->create($parentId, $data, $options);
    }
    /**
     * Create documents if not exist or update them.
     *
     * @param string $parentId
     * @param mixed  $bulkData
     * @param array  $options
     *
     * @return mixed
     */
    public function createOrUpdateBulk($parentId, $bulkData, $options = [])
    {
        $this->checkBulkData($bulkData, $options);

        $toCreate = [];
        $toUpdate = [];

        foreach ($bulkData as $i => $data) {
            unset($bulkData[$i]);
            if (isset($data['id']) && $this->has($parentId, $data['id'])) {
                $toUpdate[$i] = $data;
            } else {
                $toCreate[$i] = $data;
            }
        }

        unset($bulkData);

        $docs = [];

        if (count($toCreate)) {
            $docs += $this->createBulk($parentId, $toCreate, $options);
        }

        unset($toCreate);

        if (count($toUpdate)) {
            $docs += $this->updateBulk($parentId, $toUpdate, $options);
        }

        unset($toUpdate);

        return $docs;
    }
    /**
     * @param array $parentId
     * @param array $bulkData
     * @param array $options
     *
     * @return $this
     */
    public abstract function updateBulk($parentId, $bulkData, $options = []);
    /**
     * Create a list of documents.
     *
     * @param mixed $parentId
     * @param mixed $bulkData
     * @param array $options
     *
     * @return mixed
     */
    public abstract function createBulk($parentId, $bulkData, $options = []);
    /**
     * Create a new document.
     *
     * @param mixed $parentId
     * @param mixed $data
     * @param array $options
     *
     * @return mixed
     */
    public abstract function create($parentId, $data, $options = []);
    /**
     * @param mixed $parentId
     * @param mixed $id
     * @param array $data
     * @param array $options
     *
     * @return $this
     */
    public abstract function update($parentId, $id, $data, $options = []);
    /**
     * Test if specified document exist.
     *
     * @param mixed $parentId
     * @param mixed $id
     * @param array $options
     *
     * @return bool
     */
    public abstract function has($parentId, $id, $options = []);
    /**
     * @param mixed $bulkData
     * @param array $options
     *
     * @return $this
     *
     * @throws \Exception
     */
    protected abstract function checkBulkData($bulkData, $options = []);
}
