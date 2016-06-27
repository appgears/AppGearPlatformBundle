<?php

namespace AppGear\PlatformBundle\Service\Entity\Model;

class GetRootModel
{
    /**
     * Возвращает базовую модель для текущей
     *
     * @param Model $model Модель
     * @return Model
     */
    public function get($model)
    {
        // Пытаемся получить родительскую модель
        $parent = $model->getParent();

        // Если родительская модель существует
        if (!is_null($parent)) {

            // Ищем родительскую модель у родительской модели
            return $this->get($parent);
        }

        // Иначе текущая модель является корневой
        return $model;
    }
}