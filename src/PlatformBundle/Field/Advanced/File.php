<?php

namespace AppGear\PlatformBundle\Field\Advanced;

use AppGear\PlatformBundle\Field\Extension\Storage\MySql\TypeInterface;

class File implements TypeInterface
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
        return [
            'length' => 512
        ];
    }
}