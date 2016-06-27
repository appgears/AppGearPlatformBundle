<?php

namespace AppGear\PlatformBundle\Entity;

class Model
{
    
    /**
     * FullName
     */
    protected $fullName;
    
    /**
     * Name
     */
    protected $name;
    
    /**
     * Id
     */
    protected $id;
    
    /**
     * Relate parent
     */
    protected $parent;
    
    /**
     * Relate scope
     */
    protected $scope;
    
    /**
     * Relate children
     */
    protected $children = array();
    
    /**
     * Relate properties
     */
    protected $properties = array();
    
    /**
     * Container
     */
    protected $container;
    
    /**
     * Get fullName
     */
    public function getFullName()
    {
        return $this->container->get('ag.service.entity.model.get_full_name')->get($this);
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
     * Get related parent
     */
    public function getParent()
    {
        if (count($this->parent) === 0) {
            if (property_exists($this, 'id') && !empty($this->id)) {
                $related = $this->container->get('ag.storage')->findRelated('AppGear\\PlatformBundle\\Entity\\Model', 'AppGear\\PlatformBundle\\Entity\\Model', $this->getId(), 'parent');
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
     * Get related scope
     */
    public function getScope()
    {
        if (count($this->scope) === 0) {
            if (property_exists($this, 'id') && !empty($this->id)) {
                $related = $this->container->get('ag.storage')->findRelated('AppGear\\PlatformBundle\\Entity\\Model', 'AppGear\\PlatformBundle\\Entity\\Model\\Scope', $this->getId(), 'scope');
                if (count($related) > 0) {
                    $this->scope = $related[0];
                }
            }
        }
        return $this->scope;
    }
    
    /**
     * Set scope
     */
    public function setScope($scope)
    {
        $this->scope = $scope;
        return $this;
    }
    
    /**
     * Get related children
     */
    public function getChildren()
    {
        if (count($this->children) === 0) {
            if (property_exists($this, 'id') && !empty($this->id)) {
                $this->children = $this->container->get('ag.storage')->findRelated('AppGear\\PlatformBundle\\Entity\\Model', 'AppGear\\PlatformBundle\\Entity\\Model', $this->getId(), 'children');
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
     * Get related properties
     */
    public function getProperties()
    {
        if (count($this->properties) === 0) {
            if (property_exists($this, 'id') && !empty($this->id)) {
                $this->properties = $this->container->get('ag.storage')->findRelated('AppGear\\PlatformBundle\\Entity\\Model', 'AppGear\\PlatformBundle\\Entity\\Model\\Property', $this->getId(), 'properties');
            }
        }
        return $this->properties;
    }
    
    /**
     * Set properties
     */
    public function setProperties($properties)
    {
        $this->properties = $properties;
        return $this;
    }
    public function addItemToProperties($item)
    {
        $this->properties[] = $item;
        return $this;
    }
    public function getAllProperties()
    {
        return $this->container->get('ag.service.entity.model.get_all_properties')->get($this);
    }
    public function getAllFields()
    {
        return $this->container->get('ag.service.entity.model.get_all_fields')->get($this);
    }
    public function getAllRelationships()
    {
        return $this->container->get('ag.service.entity.model.get_all_relationships')->get($this);
    }
    public function getInstance()
    {
        return $this->container->get('ag.service.entity.model.get_instance')->get($this);
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