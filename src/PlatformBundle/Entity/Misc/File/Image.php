<?php

namespace AppGear\PlatformBundle\Entity\Misc\File;

use AppGear\PlatformBundle\Entity\Misc\File;
class Image extends File
{
    public function __toString()
    {
        return (string) $this->file;
    }
}