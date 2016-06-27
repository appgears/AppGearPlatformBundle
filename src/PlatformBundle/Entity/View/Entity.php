<?php

namespace AppGear\PlatformBundle\Entity\View;

use AppGear\PlatformBundle\Entity\View;
class Entity extends View
{
    public function __toString()
    {
        return 'Entity #' . $this->id;
    }
}