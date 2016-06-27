<?php

namespace AppGear\PlatformBundle\Entity\View\Collection\Table;

use AppGear\PlatformBundle\Entity\View\Collection\Table;
class Smart extends Table
{
    public function __toString()
    {
        return 'Smart #' . $this->id;
    }
}