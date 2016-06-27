<?php

namespace AppGear\PlatformBundle\Entity;

class Collection implements \Countable, \IteratorAggregate
{
    
    /**
     * Id
     */
    protected $id;
    
    /**
     * Name
     */
    protected $name;
    
    /**
     * Relate model
     */
    protected $model;
    
    /**
     * Relate filter
     */
    protected $filter;
    
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
     * Get related model
     */
    public function getModel()
    {
        if (count($this->model) === 0) {
            if (property_exists($this, 'id') && !empty($this->id)) {
                $related = $this->container->get('ag.storage')->findRelated('AppGear\\PlatformBundle\\Entity\\Collection', 'AppGear\\PlatformBundle\\Entity\\Model', $this->getId(), 'model');
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
     * Get related filter
     */
    public function getFilter()
    {
        if (count($this->filter) === 0) {
            if (property_exists($this, 'id') && !empty($this->id)) {
                $related = $this->container->get('ag.storage')->findRelated('AppGear\\PlatformBundle\\Entity\\Collection', 'AppGear\\PlatformBundle\\Entity\\Collection\\Filter', $this->getId(), 'filter');
                if (count($related) > 0) {
                    $this->filter = $related[0];
                }
            }
        }
        return $this->filter;
    }
    
    /**
     * Set filter
     */
    public function setFilter($filter)
    {
        $this->filter = $filter;
        return $this;
    }
    public function count()
    {
        return $this->container->get('ag.service.entity.collection.count')->count($this);
    }
    public function getIterator()
    {
        return $this->container->get('ag.service.entity.collection.get_iterator')->getIterator($this);
    }
    public function __toString()
    {
        return 'Collection #' . $this->id;
    }
    public function setContainer($container)
    {
        $this->container = $container;
        return $this;
    }
}