<?php

namespace AppGear\PlatformBundle\Entity\Controller;

use AppGear\PlatformBundle\Entity\Controller;
class View extends Controller
{
    
    /**
     * Relate view
     */
    protected $view;
    
    /**
     * Container
     */
    protected $container;
    
    /**
     * Get related view
     */
    public function getView()
    {
        if (count($this->view) === 0) {
            if (property_exists($this, 'id') && !empty($this->id)) {
                $related = $this->container->get('ag.storage')->findRelated('AppGear\\PlatformBundle\\Entity\\Controller\\View', 'AppGear\\PlatformBundle\\Entity\\View', $this->getId(), 'view');
                if (count($related) > 0) {
                    $this->view = $related[0];
                }
            }
        }
        return $this->view;
    }
    
    /**
     * Set view
     */
    public function setView($view)
    {
        $this->view = $view;
        return $this;
    }
    public function execute()
    {
        return $this->container->get('ag.service.entity.controller.view.execute')->execute($this);
    }
    public function __toString()
    {
        return 'View #' . $this->id;
    }
    public function setContainer($container)
    {
        $this->container = $container;
        return $this;
    }
}