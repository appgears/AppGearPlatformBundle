<?php

namespace AppGear\PlatformBundle\Entity\View\Collection;

use AppGear\PlatformBundle\Entity\View\Collection;
class Table extends Collection
{
    public function __toString()
    {
        return 'Table #' . $this->id;
    }
}