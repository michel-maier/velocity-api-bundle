<?php
namespace Velocity\Bundle\ApiBundle\Service\Velocity;

class IdsRegistry
{
    private $ids = [
        'crudService' => [],
        'repository' => [],
    ];
    
    public function getCruds()
    {
        return $this->ids['crudService'];
    }
    
    public function setCrud($name, $id) 
    {
        $this->ids['crudService'][$name] = $id;
    }
    
    public function getCrud($name) 
    {
        return $this->ids['crudService'][$name];
    }
    
    public function hasCrud($name)
    {
        return array_key_exists($name, $this->ids['crudService']);
    }
    
    public function getRepositories()
    {
        return $this->ids['repository'];
    }
    
    public function setRepository($name, $id)
    {
        $this->ids['repository'][$name] = $id;
    }
    
    public function getRepository($name)
    {
        return $this->ids['repository'][$name];
    }
    
    public function hasRepository($name)
    {
        return array_key_exists($name, $this->ids['repository']);
    }
}
