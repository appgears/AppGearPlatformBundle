<?php

namespace AppGear\PlatformBundle\Field\Advanced;

use AppGear\PlatformBundle\Field\Extension\Storage\MySql\TypeInterface;

class Password implements TypeInterface
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