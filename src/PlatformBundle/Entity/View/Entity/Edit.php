<?php

namespace AppGear\PlatformBundle\Entity\View\Entity;

use AppGear\PlatformBundle\Entity\View\Entity;
class Edit extends Entity
{
    
    /**
     * Container
     */
    protected $container;
    public function render($entity = null)
    {
        return $this->container->get('ag.service.entity.view.entity.edit.render')->render($this, $entity);
    }
    public function __toString()
    {
        return 'Edit #' . $this->id;
    }
    public function setContainer($container)
    {
        $this->container = $container;
        return $this;
    }
}