<?php
namespace Velocity\Bundle\ApiBundle\Service\Velocity;

/**
 * Ids Registry.
 *
 * Registry class for persistence between tag processors.
 *
 * @author Gabriele Santini <gab.santini@gmail.com>
 */
class IdsRegistry
{
    private $ids = [
        'crudService' => [],
        'repository' => [],
    ];

    /**
     * @return array
     */
    public function getCruds()
    {
        return $this->ids['crudService'];
    }
    /**
     * @param string $name
     * @param string $id
     */
    public function setCrud($name, $id)
    {
        $this->ids['crudService'][$name] = $id;
    }
    /**
     * @param string $name
     * @return string
     */
    public function getCrud($name)
    {
        return $this->ids['crudService'][$name];
    }
    /**
     * @param string $name
     * @return bool
     */
    public function hasCrud($name)
    {
        return array_key_exists($name, $this->ids['crudService']);
    }
    /**
     * @return array
     */
    public function getRepositories()
    {
        return $this->ids['repository'];
    }
    /**
     * @param string $name
     * @param string $id
     */
    public function setRepository($name, $id)
    {
        $this->ids['repository'][$name] = $id;
    }
    /**
     * @param string $name
     * @return string
     */
    public function getRepository($name)
    {
        return $this->ids['repository'][$name];
    }
    /**
     * @param string $name
     * @return bool
     */
    public function hasRepository($name)
    {
        return array_key_exists($name, $this->ids['repository']);
    }
}
