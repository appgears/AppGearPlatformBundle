<?php

namespace AppGear\PlatformBundle\Entity\Model\Property;

use AppGear\PlatformBundle\Entity\Model\Property;
class Relationship extends Property
{
    public function __toString()
    {
        return (string) $this->name;
    }
}