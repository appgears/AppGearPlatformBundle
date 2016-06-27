<?php

namespace AppGear\PlatformBundle\Field\Advanced;

use AppGear\PlatformBundle\Field\Extension\Storage\MySql\TypeInterface;

class Markdown implements TypeInterface
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