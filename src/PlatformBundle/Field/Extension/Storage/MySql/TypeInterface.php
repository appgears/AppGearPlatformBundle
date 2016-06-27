<?php

namespace AppGear\PlatformBundle\Field\Extension\Storage\MySql;

interface TypeInterface
{
    /**
     * Return MySQL column type
     *
     * @return string
     */
    public function getColumnType();

    /**
     * Return MySQL column options
     *
     * Used in the Doctrine\DBAL\Schema\Table::addColumn
     *
     * @return array
     */
    public function getColumnOptions();
}
