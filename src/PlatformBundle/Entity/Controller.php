<?php

namespace AppGear\PlatformBundle\Entity;

class Controller
{
    
    /**
     * Id
     */
    protected $id;
    
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
    public function __toString()
    {
        return 'Controller #' . $this->id;
    }
}