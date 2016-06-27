<?php

namespace AppGear\PlatformBundle\Entity\Collection;

class Filter
{
    
    /**
     * Id
     */
    protected $id;
    
    /**
     * Offset
     */
    protected $offset;
    
    /**
     * Limit
     */
    protected $limit;
    
    /**
     * Relate collections
     */
    protected $collections = array();
    
    /**
     * Relate conditions
     */
    protected $conditions = array();
    
    /**
     * Relate orders
     */
    protected $orders = array();
    
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
     * Get offset
     */
    public function getOffset()
    {
        return $this->offset;
    }
    
    /**
     * Set offset
     */
    public function setOffset($offset)
    {
        $this->offset = (int) $offset;
        return $this;
    }
    
    /**
     * Get limit
     */
    public function getLimit()
    {
        return $this->limit;
    }
    
    /**
     * Set limit
     */
    public function setLimit($limit)
    {
        $this->limit = (int) $limit;
        return $this;
    }
    
    /**
     * Get related collections
     */
    public function getCollections()
    {
        if (count($this->collections) === 0) {
            if (property_exists($this, 'id') && !empty($this->id)) {
                $this->collections = $this->container->get('ag.storage')->findRelated('AppGear\\PlatformBundle\\Entity\\Collection\\Filter', 'AppGear\\PlatformBundle\\Entity\\Collection', $this->getId(), 'collections');
            }
        }
        return $this->collections;
    }
    
    /**
     * Set collections
     */
    public function setCollections($collections)
    {
        $this->collections = $collections;
        return $this;
    }
    public function addItemToCollections($item)
    {
        $this->collections[] = $item;
        return $this;
    }
    
    /**
     * Get related conditions
     */
    public function getConditions()
    {
        if (count($this->conditions) === 0) {
            if (property_exists($this, 'id') && !empty($this->id)) {
                $this->conditions = $this->container->get('ag.storage')->findRelated('AppGear\\PlatformBundle\\Entity\\Collection\\Filter', 'AppGear\\PlatformBundle\\Entity\\Collection\\Filter\\Condition', $this->getId(), 'conditions');
            }
        }
        return $this->conditions;
    }
    
    /**
     * Set conditions
     */
    public function setConditions($conditions)
    {
        $this->conditions = $conditions;
        return $this;
    }
    public function addItemToConditions($item)
    {
        $this->conditions[] = $item;
        return $this;
    }
    
    /**
     * Get related orders
     */
    public function getOrders()
    {
        if (count($this->orders) === 0) {
            if (property_exists($this, 'id') && !empty($this->id)) {
                $this->orders = $this->container->get('ag.storage')->findRelated('AppGear\\PlatformBundle\\Entity\\Collection\\Filter', 'AppGear\\PlatformBundle\\Entity\\Collection\\Filter\\Order', $this->getId(), 'orders');
            }
        }
        return $this->orders;
    }
    
    /**
     * Set orders
     */
    public function setOrders($orders)
    {
        $this->orders = $orders;
        return $this;
    }
    public function addItemToOrders($item)
    {
        $this->orders[] = $item;
        return $this;
    }
    public function __toString()
    {
        return 'Filter #' . $this->id;
    }
    public function setContainer($container)
    {
        $this->container = $container;
        return $this;
    }
}