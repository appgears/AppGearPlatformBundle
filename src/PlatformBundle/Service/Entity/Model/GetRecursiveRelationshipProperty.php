<?php

namespace AppGear\PlatformBundle\Service\Entity\Model;

class GetRecursiveRelationshipProperty
{
    /**
     * Возвращает свойство связи, которое делает модель рекурсивной
     * (Рекурсивная модель - это модель, которая имеет связь сама с собой)
     *
     * @param Model $model Модель
     *
     * @return Relationship|null
     */
    public function get($model)
    {
        // Ищем рекурсивную связь модели
        $recursiveRelationship = null;
        foreach ($model->getAllRelationships() as $relationship)
        {
            if ($relationship->getType() == 'ManyToOne' &&
                $relationship->getTarget() !== null &&
                $relationship->getTarget()->getId() == $model->getId()) {
                return $relationship;
            }
        }

        return null;
    }
}