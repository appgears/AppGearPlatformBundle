<?php

namespace AppGear\PlatformBundle\Entity\View;

use AppGear\PlatformBundle\Entity\View;
class Collection extends View
{
    
    /**
     * ShowPagination
     */
    protected $showPagination = 1;
    
    /**
     * CurrentPage
     */
    protected $currentPage;
    
    /**
     * Relate entity
     */
    protected $entity;
    
    /**
     * Container
     */
    protected $container;
    
    /**
     * Get showPagination
     */
    public function getShowPagination()
    {
        return $this->showPagination;
    }
    
    /**
     * Set showPagination
     */
    public function setShowPagination($showPagination)
    {
        $this->showPagination = empty($showPagination) ? 0 : 1;
        return $this;
    }
    
    /**
     * Get currentPage
     */
    public function getCurrentPage()
    {
        return $this->currentPage;
    }
    
    /**
     * Set currentPage
     */
    public function setCurrentPage($currentPage)
    {
        $this->currentPage = (int) $currentPage;
        return $this;
    }
    
    /**
     * Get related entity
     */
    public function getEntity()
    {
        if (count($this->entity) === 0) {
            if (property_exists($this, 'id') && !empty($this->id)) {
                $related = $this->container->get('ag.storage')->findRelated('AppGear\\PlatformBundle\\Entity\\View\\Collection', 'AppGear\\PlatformBundle\\Entity\\Collection', $this->getId(), 'entity');
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
        return $this->container->get('ag.service.entity.view.collection.render')->render($this, $entity);
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