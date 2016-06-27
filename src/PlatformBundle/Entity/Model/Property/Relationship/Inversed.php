<?php

namespace AppGear\PlatformBundle\Entity\Model\Property\Relationship;

use AppGear\PlatformBundle\Entity\Model\Property\Relationship;
class Inversed extends Relationship
{
    
    /**
     * Relate opposite
     */
    protected $opposite;
    
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
                $related = $this->container->get('ag.storage')->findRelated('AppGear\\PlatformBundle\\Entity\\Model\\Property\\Relationship\\Inversed', 'AppGear\\PlatformBundle\\Entity\\Model\\Property\\Relationship\\Mapped', $this->getId(), 'opposite');
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
    public function getTarget()
    {
        return $this->container->get('ag.service.entity.model.property.relationship.inversed.get_target')->get($this);
    }
    public function getType()
    {
        return $this->container->get('ag.service.entity.model.property.relationship.inversed.get_type')->get($this);
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