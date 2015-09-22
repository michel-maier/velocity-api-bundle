<?php

/*
 * This file is part of the VELOCITY package.
 *
 * (c) PHPPRO <opensource@phppro.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Velocity\Bundle\ApiBundle\Storage;

use Velocity\Bundle\ApiBundle\StorageInterface;
use Velocity\Bundle\ApiBundle\Traits\ServiceTrait;
use Velocity\Bundle\ApiBundle\Traits\ServiceAware;
use Velocity\Bundle\ApiBundle\Service\DatabaseService;

/**
 * Database Storage
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
class DatabaseStorage implements StorageInterface
{
    use ServiceTrait;
    use ServiceAware\DatabaseServiceAwareTrait;
    /**
     * @param string          $collection
     * @param DatabaseService $databaseService
     */
    public function __construct($collection, DatabaseService $databaseService)
    {
        $this->setCollection($collection);
        $this->setDatabaseService($databaseService);
    }
    /**
     * @param string $collection
     *
     * @return $this
     */
    public function setCollection($collection)
    {
        return $this->setParameter('collection', $collection);
    }
    /**
     * @return string
     *
     * @throws \Exception
     */
    public function getCollection()
    {
        return $this->getParameter('collection');
    }
    /**
     * @param string $key
     * @param mixed  $value
     * @param array  $options
     *
     * @return $this
     */
    public function set($key, $value, $options = [])
    {
        $id = $this->formatId($key);

        $this->getDatabaseService()->update(
            $this->getCollection(),
            ['__id' => $id],
            $this->prepareData($id, $value),
            ['upsert' => true]
        );

        return $this;
    }
    /**
     * @param string $key
     * @param array  $options
     *
     * @return $this
     */
    public function clear($key, $options = [])
    {
        $id = $this->formatId($key);

        $this->getDatabaseService()->remove(
            $this->getCollection(),
            ['__id' => $id],
            $options
        );

        return $this;
    }
    /**
     * @param string $key
     * @param array  $options
     *
     * @return string
     *
     * @throws \Exception
     */
    public function get($key, $options = [])
    {
        $doc = $this->getDatabaseService()->findOne($this->getCollection(), ['__id' => $this->formatId($key)]);

        if (!$doc) {
            throw $this->createNotFoundException(
                "Unknown document '%s' in collection '%s'",
                $key,
                $this->getCollection()
            );
        }

        return $this->cleanData($doc);
    }
    /**
     * @param string $key
     * @param array  $options
     *
     * @return bool
     */
    public function has($key, $options = [])
    {
        return null !== $this->getDatabaseService()
            ->findOne($this->getCollection(), ['__id' => $this->formatId($key)], ['__id' => true])
        ;
    }
    /**
     * @param string $id
     *
     * @return string
     */
    protected function formatId($id)
    {
        return md5($id);
    }
    /**
     * @param string $id
     * @param mixed  $value
     *
     * @return array
     */
    protected function prepareData($id, $value)
    {
        if (is_object($value) || is_array($value)) {
            $data = (array) $value;
        } else {
            $data = ['data' => $value];
        }

        return ['__id' => $id, '__saveDate' => date_create()->format('c')] + $data;
    }
    /**
     * @param array $data
     *
     * @return mixed
     */
    protected function cleanData($data)
    {
        unset($data['__id'], $data['__saveDate'], $data['_id']);

        if (1 === count($data) && isset($data['data'])) {
            return $data['data'];
        }

        return $data;
    }
}
