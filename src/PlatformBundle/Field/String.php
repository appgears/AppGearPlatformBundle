<?php

namespace AppGear\PlatformBundle\Field;

use AppGear\PlatformBundle\Field\Extension\Storage\MySql\TypeInterface;

class String implements TypeInterface
{
    /**
     * {@inheritdoc}
     */
    public function getColumnType()
    {
        return 'string';
    }

    /**
     * {@inheritdoc}
     */
    public function getColumnOptions()
    {
        return [];
    }
}