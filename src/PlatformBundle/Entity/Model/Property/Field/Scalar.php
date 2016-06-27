<?php

namespace AppGear\PlatformBundle\Entity\Model\Property\Field;

use AppGear\PlatformBundle\Entity\Model\Property\Field;
class Scalar extends Field
{
    
    /**
     * ServiceName
     */
    protected $serviceName;
    
    /**
     * DefaultValue
     */
    protected $defaultValue;
    
    /**
     * Representation
     */
    protected $representation;
    
    /**
     * Get serviceName
     */
    public function getServiceName()
    {
        return $this->serviceName;
    }
    
    /**
     * Set serviceName
     */
    public function setServiceName($serviceName)
    {
        $this->serviceName = $serviceName;
        return $this;
    }
    
    /**
     * Get defaultValue
     */
    public function getDefaultValue()
    {
        return $this->defaultValue;
    }
    
    /**
     * Set defaultValue
     */
    public function setDefaultValue($defaultValue)
    {
        $this->defaultValue = $defaultValue;
        return $this;
    }
    
    /**
     * Get representation
     */
    public function getRepresentation()
    {
        return $this->representation;
    }
    
    /**
     * Set representation
     */
    public function setRepresentation($representation)
    {
        $this->representation = empty($representation) ? 0 : 1;
        return $this;
    }
    public function __toString()
    {
        return (string) $this->name;
    }
}