<?php

namespace AppGear\PlatformBundle\Field;

use AppGear\PlatformBundle\Field\Extension\Storage\MySql\TypeInterface;

class Integer implements TypeInterface
{
    /**
     * {@inheritdoc}
     */
    public function getColumnType()
    {
        return 'integer';
    }

    /**
     * {@inheritdoc}
     */
    public function getColumnOptions()
    {
        return [];
    }
}