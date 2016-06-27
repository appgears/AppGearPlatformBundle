<?php

namespace AppGear\PlatformBundle\Entity\View\Collection;

use AppGear\PlatformBundle\Entity\View\Collection;
class News extends Collection
{
    
    /**
     * Relate itemView
     */
    protected $itemView;
    
    /**
     * Container
     */
    protected $container;
    
    /**
     * Get related itemView
     */
    public function getItemView()
    {
        if (count($this->itemView) === 0) {
            if (property_exists($this, 'id') && !empty($this->id)) {
                $related = $this->container->get('ag.storage')->findRelated('AppGear\\PlatformBundle\\Entity\\View\\Collection\\News', 'AppGear\\PlatformBundle\\Entity\\View', $this->getId(), 'itemView');
                if (count($related) > 0) {
                    $this->itemView = $related[0];
                }
            }
        }
        return $this->itemView;
    }
    
    /**
     * Set itemView
     */
    public function setItemView($itemView)
    {
        $this->itemView = $itemView;
        return $this;
    }
    public function __toString()
    {
        return 'News #' . $this->id;
    }
    public function setContainer($container)
    {
        $this->container = $container;
        return $this;
    }
}