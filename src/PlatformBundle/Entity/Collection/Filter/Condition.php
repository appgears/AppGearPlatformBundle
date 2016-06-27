<?php

namespace AppGear\PlatformBundle\Entity\Collection\Filter;

class Condition
{
    
    /**
     * Field
     */
    protected $field;
    
    /**
     * Operator
     */
    protected $operator;
    
    /**
     * Value
     */
    protected $value;
    
    /**
     * Id
     */
    protected $id;
    
    /**
     * Relate filter
     */
    protected $filter;
    
    /**
     * Container
     */
    protected $container;
    
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
     * Get operator
     */
    public function getOperator()
    {
        return $this->operator;
    }
    
    /**
     * Set operator
     */
    public function setOperator($operator)
    {
        $this->operator = $operator;
        return $this;
    }
    
    /**
     * Get value
     */
    public function getValue()
    {
        return $this->value;
    }
    
    /**
     * Set value
     */
    public function setValue($value)
    {
        $this->value = $value;
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
     * Get related filter
     */
    public function getFilter()
    {
        if (count($this->filter) === 0) {
            if (property_exists($this, 'id') && !empty($this->id)) {
                $related = $this->container->get('ag.storage')->findRelated('AppGear\\PlatformBundle\\Entity\\Collection\\Filter\\Condition', 'AppGear\\PlatformBundle\\Entity\\Collection\\Filter', $this->getId(), 'filter');
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
        return 'Condition #' . $this->id;
    }
    public function setContainer($container)
    {
        $this->container = $container;
        return $this;
    }
}