<?php

namespace AppGear\PlatformBundle\Entity\Model;

class Scope
{
    
    /**
     * Id
     */
    protected $id;
    
    /**
     * Name
     */
    protected $name;
    
    /**
     * FullName
     */
    protected $fullName;
    
    /**
     * Relate parent
     */
    protected $parent;
    
    /**
     * Relate children
     */
    protected $children = array();
    
    /**
     * Relate models
     */
    protected $models = array();
    
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
     * Get name
     */
    public function getName()
    {
        return $this->name;
    }
    
    /**
     * Set name
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }
    
    /**
     * Get fullName
     */
    public function getFullName()
    {
        return $this->container->get('ag.service.entity.model.scope.get_full_name')->get($this);
    }
    
    /**
     * Get related parent
     */
    public function getParent()
    {
        if (count($this->parent) === 0) {
            if (property_exists($this, 'id') && !empty($this->id)) {
                $related = $this->container->get('ag.storage')->findRelated('AppGear\\PlatformBundle\\Entity\\Model\\Scope', 'AppGear\\PlatformBundle\\Entity\\Model\\Scope', $this->getId(), 'parent');
                if (count($related) > 0) {
                    $this->parent = $related[0];
                }
            }
        }
        return $this->parent;
    }
    
    /**
     * Set parent
     */
    public function setParent($parent)
    {
        $this->parent = $parent;
        return $this;
    }
    
    /**
     * Get related children
     */
    public function getChildren()
    {
        if (count($this->children) === 0) {
            if (property_exists($this, 'id') && !empty($this->id)) {
                $this->children = $this->container->get('ag.storage')->findRelated('AppGear\\PlatformBundle\\Entity\\Model\\Scope', 'AppGear\\PlatformBundle\\Entity\\Model\\Scope', $this->getId(), 'children');
            }
        }
        return $this->children;
    }
    
    /**
     * Set children
     */
    public function setChildren($children)
    {
        $this->children = $children;
        return $this;
    }
    public function addItemToChildren($item)
    {
        $this->children[] = $item;
        return $this;
    }
    
    /**
     * Get related models
     */
    public function getModels()
    {
        if (count($this->models) === 0) {
            if (property_exists($this, 'id') && !empty($this->id)) {
                $this->models = $this->container->get('ag.storage')->findRelated('AppGear\\PlatformBundle\\Entity\\Model\\Scope', 'AppGear\\PlatformBundle\\Entity\\Model', $this->getId(), 'models');
            }
        }
        return $this->models;
    }
    
    /**
     * Set models
     */
    public function setModels($models)
    {
        $this->models = $models;
        return $this;
    }
    public function addItemToModels($item)
    {
        $this->models[] = $item;
        return $this;
    }
    public function getParentOrSelfNames()
    {
        return $this->container->get('ag.service.entity.model.scope.get_parent_or_self_names')->get($this);
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