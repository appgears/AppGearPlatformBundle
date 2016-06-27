<?php

namespace AppGear\PlatformBundle\Entity\View\Entity;

use AppGear\PlatformBundle\Entity\View\Entity;
class Detail extends Entity
{
    public function __toString()
    {
        return 'Detail #' . $this->id;
    }
}