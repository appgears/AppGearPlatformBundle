<?php

namespace AppGear\PlatformBundle\Entity\View\Entity\Edit;

use AppGear\PlatformBundle\Entity\View\Entity\Edit;
class Smart extends Edit
{
    public function __toString()
    {
        return 'Smart #' . $this->id;
    }
}