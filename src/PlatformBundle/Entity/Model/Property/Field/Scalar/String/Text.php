<?php

namespace AppGear\PlatformBundle\Entity\Model\Property\Field\Scalar\String;

use AppGear\PlatformBundle\Entity\Model\Property\Field\Scalar\String;
class Text extends String
{
    public function __toString()
    {
        return (string) $this->name;
    }
}