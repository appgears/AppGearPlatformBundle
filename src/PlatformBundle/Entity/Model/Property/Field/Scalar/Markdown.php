<?php

namespace AppGear\PlatformBundle\Entity\Model\Property\Field\Scalar;

use AppGear\PlatformBundle\Entity\Model\Property\Field\Scalar;
class Markdown extends Scalar
{
    public function __toString()
    {
        return (string) $this->name;
    }
}