<?php

namespace AppGear\PlatformBundle\Entity\View\Container;

use AppGear\PlatformBundle\Entity\View\Container;
class Admin extends Container
{
    
    /**
     * Container
     */
    protected $container;
    public function render($entity = null)
    {
        return $this->container->get('ag.service.entity.view.container.admin.render')->render($this, $entity);
    }
    public function __toString()
    {
        return 'Admin #' . $this->id;
    }
    public function setContainer($container)
    {
        $this->container = $container;
        return $this;
    }
}