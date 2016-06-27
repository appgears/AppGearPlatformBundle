<?php

namespace AppGear\PlatformBundle\Entity\Security;

class User
{
    
    /**
     * Container
     */
    protected $container;
    
    /**
     * Id
     */
    protected $id = '';
    public function setContainer($container)
    {
        $this->container = $container;
        return $this;
    }
    
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
        return 'User #' . $this->id;
    }
}