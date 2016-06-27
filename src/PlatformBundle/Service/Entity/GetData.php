<?php

namespace AppGear\PlatformBundle\Service\Entity;

use AppGear\PlatformBundle\Entity\Model;
use AppGear\PlatformBundle\Entity\Model\Property\Field;

class GetData
{
    /**
     * Получает данные из инстанца модели
     *
     * @param object $instance Инстанц модели
     * @param Model $model Сама модель
     * @param bool $ignoreParentModels Надо ли игнорировать родительские модели?
     *                                 Если true - то вернутся данные только относящиеся к самой модели
     *
     * @throws \Exception
     * @return array
     */
    public function get($instance, Model $model, $ignoreParentModels=false)
    {
        $data = array(
            'fields' => array(),
            'relationships' => array()
        );

        if ($ignoreParentModels) {
            $fields = [];
            $relationships = [];

            foreach ($model->getProperties() as $property) {
                if ($property instanceof Field) {
                    $fields[] = $property;
                } else {
                    $relationships[] = $property;
                }
            }
        } else {
            $fields        = $model->getAllFields();
            $relationships = $model->getAllRelationships();
        }

        // Перебираем все поля модели
        foreach ($fields as $field) {

            // Геттер для данного поля
            $getter = 'get' . ucfirst($field->getName());

            // Получаем значение из поля
            $data['fields'][$field->getName()] = $instance->$getter();
        }

        // Перебираем все связи модели
        foreach ($relationships as $relationship) {

            // Геттер для связанных сущностей
            $getter = 'get' . ucfirst($relationship->getName());

            // Получаем связанные сущности
            $related = $instance->$getter();

            // Получаем идентификаторы связанных сущностей
            if ($relationship->getType() == 'OneToMany') {
                $data['relationships'][$relationship->getName()] = array();

                foreach ($related as $relatedItem) {
                    $data['relationships'][$relationship->getName()][] = $relatedItem->getId();
                }
            } elseif (in_array($relationship->getType(), array('ManyToOne', 'OneToOne'))) {
                $data['relationships'][$relationship->getName()] = ($related !== null) ? $related->getId() : null;
            } else {
                throw new \Exception('Неизвестный тип связи: ' . $relationship->getType());
            }
        }

        return $data;
    }
}