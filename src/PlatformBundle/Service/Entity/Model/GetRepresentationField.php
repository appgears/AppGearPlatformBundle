<?php

namespace AppGear\PlatformBundle\Service\Entity\Model;

class GetRepresentationField
{
    /**
     * Возвращает репрезентативное поле
     *
     * @param Model $model
     *
     * @return array
     */
    public function get($model)
    {
        foreach ($model->getAllFields() as $field) {
            if ($field->getRepresentation() == 1) {
                return $field;
            }
        }

        return null;
    }
}