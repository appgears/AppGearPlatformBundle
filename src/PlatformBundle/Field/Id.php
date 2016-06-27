<?php

namespace AppGear\PlatformBundle\Field;

use AppGear\PlatformBundle\Field\Extension\Storage\MySql\TypeInterface;

class Id implements TypeInterface
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
        return [
            'autoincrement' => true,
            'unsigned' => true,
            'notNull' => true
        ];
    }
}