<?php

namespace AppGear\PlatformBundle\Entity\Controller;

use AppGear\PlatformBundle\Entity\Controller;
class Save extends Controller
{
    
    /**
     * Relate entity
     */
    protected $entity;
    
    /**
     * Container
     */
    protected $container;
    
    /**
     * Get related entity
     */
    public function getEntity()
    {
        return $this->entity;
    }
    
    /**
     * Set entity
     */
    public function setEntity($entity)
    {
        $this->entity = $entity;
        return $this;
    }
    public function execute()
    {
        return $this->container->get('ag.service.entity.controller.save.execute')->execute($this);
    }
    public function __toString()
    {
        return 'Save #' . $this->id;
    }
    public function setContainer($container)
    {
        $this->container = $container;
        return $this;
    }
}