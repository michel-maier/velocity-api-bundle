<?php
namespace Velocity\Bundle\ApiBundle\Service\Velocity;

class RepositoryIds
{
    private $ids = [];
    
    public function set($name, $id) 
    {
        $this->ids[$name] = $id;
    }
    
    public function get($name) 
    {
        return $this->ids[$name];
    }
    
    public function has($name)
    {
        return array_key_exists($name, $this->ids);
    }
}
