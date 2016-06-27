<?php

namespace AppGear\PlatformBundle\Entity;

class View
{
    
    /**
     * Id
     */
    protected $id;
    
    /**
     * Template
     */
    protected $template;
    
    /**
     * RoutePrefix
     */
    protected $routePrefix = 'view_';
    
    /**
     * Relate entity
     */
    protected $entity;
    
    /**
     * Container
     */
    protected $container;
    
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
     * Get template
     */
    public function getTemplate()
    {
        return $this->template;
    }
    
    /**
     * Set template
     */
    public function setTemplate($template)
    {
        $this->template = $template;
        return $this;
    }
    
    /**
     * Get routePrefix
     */
    public function getRoutePrefix()
    {
        return $this->routePrefix;
    }
    
    /**
     * Set routePrefix
     */
    public function setRoutePrefix($routePrefix)
    {
        $this->routePrefix = $routePrefix;
        return $this;
    }
    
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
    public function render($entity = null)
    {
        return $this->container->get('ag.service.entity.view.render')->render($this, $entity);
    }
    public function __toString()
    {
        return 'View #' . $this->id;
    }
    public function setContainer($container)
    {
        $this->container = $container;
        return $this;
    }
}