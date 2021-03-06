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

/**
 * Create or delete service trait.
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
trait CreateOrDeleteServiceTrait
{
    /**
     * Create documents if not exist or delete them.
     *
     * @param string $pParentId
     * @param string $parentId
     * @param mixed  $bulkData
     * @param array  $options
     *
     * @return mixed
     */
    public function createOrDeleteBulk($pParentId, $parentId, $bulkData, $options = [])
    {
        $this->checkBulkData($bulkData, $options);

        $toCreate = [];
        $toDelete = [];

        foreach ($bulkData as $i => $data) {
            if (isset($data['id']) && $this->has($pParentId, $parentId, $data['id'])) {
                $toDelete[$i] = $data;
            } else {
                $toCreate[$i] = $data;
            }
            unset($bulkData[$i]);
        }

        unset($bulkData);

        $docs = [];

        if (count($toCreate)) {
            $docs += $this->createBulk($pParentId, $parentId, $toCreate, $options);
        }

        unset($toCreate);

        if (count($toDelete)) {
            $docs += $this->deleteBulk($pParentId, $parentId, $toDelete, $options);
        }

        unset($toDelete);

        return $docs;
    }
    /**
     * @param mixed $pParentId
     * @param mixed $parentId
     * @param array $bulkData
     * @param array $options
     *
     * @return $this
     */
    public abstract function deleteBulk($pParentId, $parentId, $bulkData, $options = []);
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
    public abstract function createBulk($pParentId, $parentId, $bulkData, $options = []);
    /**
     * Test if specified document exist.
     *
     * @param mixed $pParentId
     * @param mixed $parentId
     * @param mixed $id
     * @param array $options
     *
     * @return bool
     */
    public abstract function has($pParentId, $parentId, $id, $options = []);
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
