<?php

namespace AppGear\PlatformBundle\Entity\Model\Property;

class Group
{
    
    /**
     * Id
     */
    protected $id = '';
    
    /**
     * Name
     */
    protected $name = '';
    
    /**
     * Get id
     */
    public function getId()
    {
        return $this->id;
    }
    
    /**
     * Set id
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }
    
    /**
     * Get name
     */
    public function getName()
    {
        return $this->name;
    }
    
    /**
     * Set name
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }
    public function __toString()
    {
        return (string) $this->name;
    }
}