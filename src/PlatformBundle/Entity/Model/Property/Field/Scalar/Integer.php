<?php

namespace AppGear\PlatformBundle\Entity\Model\Property\Field\Scalar;

use AppGear\PlatformBundle\Entity\Model\Property\Field\Scalar;
class Integer extends Scalar
{
    
    /**
     * Unit
     */
    protected $unit = '';
    
    /**
     * Unit_plural
     */
    protected $unit_plural = '';
    
    /**
     * Get unit
     */
    public function getUnit()
    {
        return $this->unit;
    }
    
    /**
     * Set unit
     */
    public function setUnit($unit)
    {
        $this->unit = $unit;
        return $this;
    }
    
    /**
     * Get unit_plural
     */
    public function getUnit_plural()
    {
        return $this->unit_plural;
    }
    
    /**
     * Set unit_plural
     */
    public function setUnit_plural($unit_plural)
    {
        $this->unit_plural = $unit_plural;
        return $this;
    }
    public function __toString()
    {
        return (string) $this->name;
    }
}