<?php

namespace AppGear\PlatformBundle\Entity\Collection\Filter;

class Order
{
    
    /**
     * Id
     */
    protected $id;
    
    /**
     * Field
     */
    protected $field;
    
    /**
     * Direction
     */
    protected $direction;
    
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
     * Get field
     */
    public function getField()
    {
        return $this->field;
    }
    
    /**
     * Set field
     */
    public function setField($field)
    {
        $this->field = $field;
        return $this;
    }
    
    /**
     * Get direction
     */
    public function getDirection()
    {
        return $this->direction;
    }
    
    /**
     * Set direction
     */
    public function setDirection($direction)
    {
        $this->direction = $direction;
        return $this;
    }
    
    /**
     * Get related filter
     */
    public function getFilter()
    {
        if (count($this->filter) === 0) {
            if (property_exists($this, 'id') && !empty($this->id)) {
                $related = $this->container->get('ag.storage')->findRelated('AppGear\\PlatformBundle\\Entity\\Collection\\Filter\\Order', 'AppGear\\PlatformBundle\\Entity\\Collection\\Filter', $this->getId(), 'filter');
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
    public function __toString()
    {
        return 'Order #' . $this->id;
    }
    public function setContainer($container)
    {
        $this->container = $container;
        return $this;
    }
}