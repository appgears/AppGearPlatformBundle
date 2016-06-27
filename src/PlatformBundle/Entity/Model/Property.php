<?php

namespace AppGear\PlatformBundle\Entity\Model;

class Property
{
    
    /**
     * Name
     */
    protected $name;
    
    /**
     * Id
     */
    protected $id;
    
    /**
     * Relate model
     */
    protected $model;
    
    /**
     * Relate group
     */
    protected $group;
    
    /**
     * Container
     */
    protected $container;
    
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
     * Get related model
     */
    public function getModel()
    {
        if (count($this->model) === 0) {
            if (property_exists($this, 'id') && !empty($this->id)) {
                $related = $this->container->get('ag.storage')->findRelated('AppGear\\PlatformBundle\\Entity\\Model\\Property', 'AppGear\\PlatformBundle\\Entity\\Model', $this->getId(), 'model');
                if (count($related) > 0) {
                    $this->model = $related[0];
                }
            }
        }
        return $this->model;
    }
    
    /**
     * Set model
     */
    public function setModel($model)
    {
        $this->model = $model;
        return $this;
    }
    
    /**
     * Get related group
     */
    public function getGroup()
    {
        if (count($this->group) === 0) {
            if (property_exists($this, 'id') && !empty($this->id)) {
                $related = $this->container->get('ag.storage')->findRelated('AppGear\\PlatformBundle\\Entity\\Model\\Property', 'AppGear\\PlatformBundle\\Entity\\Model\\Property\\Group', $this->getId(), 'group');
                if (count($related) > 0) {
                    $this->group = $related[0];
                }
            }
        }
        return $this->group;
    }
    
    /**
     * Set group
     */
    public function setGroup($group)
    {
        $this->group = $group;
        return $this;
    }
    public function __toString()
    {
        return (string) $this->name;
    }
    public function setContainer($container)
    {
        $this->container = $container;
        return $this;
    }
}