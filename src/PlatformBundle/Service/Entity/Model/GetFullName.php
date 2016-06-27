<?php

namespace AppGear\PlatformBundle\Service\Entity\Model;

class GetFullName
{
    /**
     *  Возвращает полное имя модели (с учетом scope)
     *
     * @param Model $model
     *
     * @return string
     */
    public function get($model)
    {
        $scopeFullName = '';
        if ($scope = $model->getScope()) {
            $scopeFullName = $model->getScope()->getFullName() . '\\';
        }
        return  $scopeFullName . $model->getName();
    }
}