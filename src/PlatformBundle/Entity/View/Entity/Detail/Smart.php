<?php

namespace AppGear\PlatformBundle\Entity\View\Entity\Detail;

use AppGear\PlatformBundle\Entity\View\Entity\Detail;
class Smart extends Detail
{
    public function __toString()
    {
        return 'Smart #' . $this->id;
    }
}