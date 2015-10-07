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

use Velocity\Bundle\ApiBundle\RepositoryInterface;

/**
 * Find service trait.
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
trait FindServiceTrait
{
    /**
     * Count documents matching the specified criteria.
     *
     * @param string $parentId
     * @param mixed  $criteria
     * @param array  $options
     *
     * @return mixed
     */
    public function count($parentId, $criteria = [], $options = [])
    {
        if (!$this->getRepository()->hasProperty($parentId, $this->getRepoKey(), $options)) {
            return 0;
        }

        $items = $this->getRepository()->getListProperty($parentId, $this->getRepoKey(), $options);

        $this->filterItems($items, $criteria);

        unset($criteria);

        return count($items);
    }
    /**
     * Retrieve the documents matching the specified criteria.
     *
     * @param string   $parentId
     * @param array    $criteria
     * @param array    $fields
     * @param null|int $limit
     * @param int      $offset
     * @param array    $sorts
     * @param array    $options
     *
     * @return mixed
     */
    public function find($parentId, $criteria = [], $fields = [], $limit = null, $offset = 0, $sorts = [], $options = [])
    {
        if (!$this->getRepository()->hasProperty($parentId, $this->getRepoKey())) {
            return [];
        }

        $items = $this->getRepository()->getListProperty($parentId, $this->getRepoKey());

        $this->sortItems($items, $sorts, $options);
        $this->filterItems($items, $criteria, $fields, null, $options);
        $this->paginateItems($items, $limit, $offset, $options);

        foreach ($items as $k => $v) {
            $items[$k] = $this->callback($parentId, 'fetched', $this->convertToModel($v, $options), $options);
        }

        return $items;
    }
    /**
     * Retrieve the documents matching the specified criteria.
     *
     * @param mixed    $fieldName
     * @param mixed    $fieldValue
     * @param array    $criteria
     * @param array    $fields
     * @param null|int $limit
     * @param int      $offset
     * @param array    $sorts
     * @param array    $options
     *
     * @return mixed
     */
    public function findBy($fieldName, $fieldValue, $criteria = [], $fields = [], $limit = null, $offset = 0, $sorts = [], $options = [])
    {
        return $this->find(
            $this->getRepository()->getBy($fieldName, $fieldValue, ['_id'])['_id'],
            $criteria,
            $fields,
            $limit,
            $offset,
            $sorts,
            $options
        );
    }
    /**
     * Retrieve the documents matching the specified criteria and return a page with total count.
     *
     * @param string   $parentId
     * @param array    $criteria
     * @param array    $fields
     * @param null|int $limit
     * @param int      $offset
     * @param array    $sorts
     * @param array    $options
     *
     * @return mixed
     */
    public function findWithTotal($parentId, $criteria = [], $fields = [], $limit = null, $offset = 0, $sorts = [], $options = [])
    {
        return [
            $this->find($parentId, $criteria, $fields, $limit, $offset, $sorts, $options),
            $this->count($parentId, $criteria, $options),
        ];
    }
    /**
     * @param array $ids
     * @param array $options
     *
     * @return string
     */
    public abstract function getRepoKey(array $ids = [], $options = []);
    /**
     * @return RepositoryInterface
     */
    public abstract function getRepository();
    /**
     * Execute the registered callback and return the updated subject.
     *
     * @param string $parentId
     * @param string $key
     * @param mixed  $subject
     * @param array  $options
     *
     * @return mixed
     */
    protected abstract function callback($parentId, $key, $subject = null, $options = []);
    /**
     * Convert provided data (array) to a model.
     *
     * @param array $data
     * @param array $options
     *
     * @return mixed
     */
    protected abstract function convertToModel(array $data, $options = []);
    /**
     * @param array    $items
     * @param array    $criteria
     * @param array    $fields
     * @param \Closure $eachCallback
     * @param array    $options
     *
     * @return $this
     */
    protected abstract function filterItems(&$items, $criteria = [], $fields = [], \Closure $eachCallback = null, $options = []);
    /**
     * @param array $items
     * @param int   $limit
     * @param int   $offset
     * @param array $options
     *
     * @return $this
     */
    protected abstract function paginateItems(&$items, $limit, $offset, $options = []);
    /**
     * @param array $items
     * @param array $sorts
     * @param array $options
     *
     * @return $this
     */
    protected abstract function sortItems(&$items, $sorts = [], $options = []);
}
