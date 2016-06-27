<?php

namespace AppGear\PlatformBundle\Field;

use AppGear\PlatformBundle\Field\Extension\Storage\MySql\TypeInterface;

class Text implements TypeInterface
{
    /**
     * {@inheritdoc}
     */
    public function getColumnType()
    {
        return 'text';
    }

    /**
     * {@inheritdoc}
     */
    public function getColumnOptions()
    {
        return [];
    }
}