<?php

namespace AppGear\PlatformBundle\Field;

use AppGear\PlatformBundle\Field\Extension\Storage\MySql\TypeInterface;

class Boolean implements TypeInterface
{
    /**
     * {@inheritdoc}
     */
    public function getColumnType()
    {
        return 'boolean';
    }

    /**
     * {@inheritdoc}
     */
    public function getColumnOptions()
    {
        return [];
    }
}