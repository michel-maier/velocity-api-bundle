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
     * @param mixed $criteria
     * @param array $options
     *
     * @return int
     */
    public function count($criteria = [], $options = [])
    {
        return $this->getRepository()->count($criteria, $options);
    }
    /**
     * Retrieve the documents matching the specified criteria.
     *
     * @param array    $criteria
     * @param array    $fields
     * @param null|int $limit
     * @param int      $offset
     * @param array    $sorts
     * @param array    $options
     *
     * @return mixed
     */
    public function find($criteria = [], $fields = [], $limit = null, $offset = 0, $sorts = [], $options = [])
    {
        $cursor = $this->getRepository()->find($criteria, $fields, $limit, $offset, $sorts, $options);
        $data   = [];

        unset($criteria, $fields, $limit, $offset, $sorts);

        foreach ($cursor as $k => $v) {
            $data[$k] = $this->callback('fetched', $this->convertToModel($v, $options), $options);
        }

        unset($cursor);

        return $data;
    }
    /**
     * @param array $criteria
     * @param array $fields
     * @param int   $offset
     * @param array $sorts
     * @param array $options
     *
     * @return mixed|null
     */
    public function findOne($criteria = [], $fields = [], $offset = 0, $sorts = [], $options = [])
    {
        $items = $this->find($criteria, $fields, 1, $offset, $sorts, $options);

        if (!count($items)) {
            return null;
        }

        return array_shift($items);
    }
    /**
     * Retrieve the documents matching the specified criteria and return a page with total count.
     *
     * @param array    $criteria
     * @param array    $fields
     * @param null|int $limit
     * @param int      $offset
     * @param array    $sorts
     * @param array    $options
     *
     * @return mixed
     */
    public function findWithTotal($criteria = [], $fields = [], $limit = null, $offset = 0, $sorts = [], $options = [])
    {
        return [
            $this->find($criteria, $fields, $limit, $offset, $sorts, $options),
            $this->count($criteria, $options),
        ];
    }
    /**
     * @return RepositoryInterface
     */
    public abstract function getRepository();
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
     * Convert provided data (array) to a model.
     *
     * @param array $data
     * @param array $options
     *
     * @return mixed
     */
    protected abstract function convertToModel(array $data, $options = []);
}
