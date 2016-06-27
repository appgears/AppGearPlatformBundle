<?php

namespace AppGear\PlatformBundle\Entity\View;

use AppGear\PlatformBundle\Entity\View;
class Container extends View
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
        if (count($this->entity) === 0) {
            if (property_exists($this, 'id') && !empty($this->id)) {
                $related = $this->container->get('ag.storage')->findRelated('AppGear\\PlatformBundle\\Entity\\View\\Container', 'AppGear\\PlatformBundle\\Entity\\View', $this->getId(), 'entity');
                if (count($related) > 0) {
                    $this->entity = $related[0];
                }
            }
        }
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
        return $this->container->get('ag.service.entity.view.container.render')->render($this, $entity);
    }
    public function __toString()
    {
        return 'Container #' . $this->id;
    }
    public function setContainer($container)
    {
        $this->container = $container;
        return $this;
    }
}