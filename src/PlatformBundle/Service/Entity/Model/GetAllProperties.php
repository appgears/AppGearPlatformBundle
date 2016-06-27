<?php

namespace AppGear\PlatformBundle\Service\Entity\Model;

class GetAllProperties
{
    /**
     * Возвращает все свойства модели, а также все свойства родительских моделей
     *
     * @param Model $model Model
     *
     * @return array
     */
    public function get($model)
    {
        $result = [];

        if ($parent = $model->getParent()) {
            $result = $this->get($parent);
        }

        foreach ($model->getProperties() as $property) {
            $result[$property->getName()] = $property;
        }

        return $result;
    }
}