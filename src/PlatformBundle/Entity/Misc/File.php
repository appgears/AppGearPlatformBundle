<?php

namespace AppGear\PlatformBundle\Entity\Misc;

class File
{
    
    /**
     * Id
     */
    protected $id;
    
    /**
     * File
     */
    protected $file = '';
    
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
     * Get file
     */
    public function getFile()
    {
        return $this->file;
    }
    
    /**
     * Set file
     */
    public function setFile($file)
    {
        $this->file = $file;
        return $this;
    }
    public function __toString()
    {
        return (string) $this->file;
    }
}