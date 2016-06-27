<?php

namespace AppGear\PlatformBundle\Storage\Mysql;

class Utils
{
    /**
     * Build table name for model
     *
     * @param Model $model Model
     *
     * @return string Table name
     */
    public static function buildModelTableName($model)
    {
        return str_replace(['Bundle\\Entity\\', '\\'], '', $model->getFullName());
    }
}