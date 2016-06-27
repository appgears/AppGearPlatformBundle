<?php

namespace AppGear\PlatformBundle\Entity\Model\Property\Relationship;

use AppGear\PlatformBundle\Entity\Model\Property\Relationship;
class Mapped extends Relationship
{
    
    /**
     * Relate opposite
     */
    protected $opposite;
    
    /**
     * Type
     */
    protected $type;
    
    /**
     * Relate target
     */
    protected $target;
    
    /**
     * Container
     */
    protected $container;
    
    /**
     * Get related opposite
     */
    public function getOpposite()
    {
        if (count($this->opposite) === 0) {
            if (property_exists($this, 'id') && !empty($this->id)) {
                $related = $this->container->get('ag.storage')->findRelated('AppGear\\PlatformBundle\\Entity\\Model\\Property\\Relationship\\Mapped', 'AppGear\\PlatformBundle\\Entity\\Model\\Property\\Relationship\\Inversed', $this->getId(), 'opposite');
                if (count($related) > 0) {
                    $this->opposite = $related[0];
                }
            }
        }
        return $this->opposite;
    }
    
    /**
     * Set opposite
     */
    public function setOpposite($opposite)
    {
        $this->opposite = $opposite;
        return $this;
    }
    
    /**
     * Get type
     */
    public function getType()
    {
        return $this->type;
    }
    
    /**
     * Set type
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }
    
    /**
     * Get related target
     */
    public function getTarget()
    {
        if (count($this->target) === 0) {
            if (property_exists($this, 'id') && !empty($this->id)) {
                $related = $this->container->get('ag.storage')->findRelated('AppGear\\PlatformBundle\\Entity\\Model\\Property\\Relationship\\Mapped', 'AppGear\\PlatformBundle\\Entity\\Model', $this->getId(), 'target');
                if (count($related) > 0) {
                    $this->target = $related[0];
                }
            }
        }
        return $this->target;
    }
    
    /**
     * Set target
     */
    public function setTarget($target)
    {
        $this->target = $target;
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