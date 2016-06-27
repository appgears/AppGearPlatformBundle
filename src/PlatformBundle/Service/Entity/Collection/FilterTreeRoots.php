<?php

namespace AppGear\PlatformBundle\Service\Entity\Collection;

use AppGear\PlatformBundle\Entity\Collection;
use AppGear\PlatformBundle\Service\Entity\Model\GetRecursiveRelationshipProperty;

class FilterTreeRoots
{
    /**
     * @var GetRecursiveRelationshipProperty
     */
    protected $recursiveRelationshipPropertyGetter;

    /**
     * @param GetRecursiveRelationshipProperty $recursiveRelationshipPropertyGetter
     */
    public function __construct(GetRecursiveRelationshipProperty $recursiveRelationshipPropertyGetter)
    {
        $this->recursiveRelationshipPropertyGetter = $recursiveRelationshipPropertyGetter;
    }


    /**
     * Фильтрует элементы коллекции, оставляя только корневые элементы
     *
     * (Подразуемевается что модель коллекции имеет рекурсивную связь сама с собой, и поэтому структура объектов модели
     * представляет собой дерево)
     *
     * Для данной операции из хранилища загружаются все элементы коллекции
     *
     * @param Collection $collection Коллекция
     *
     * @throws \RuntimeException
     * @return array Набор корневых элементов коллекции
     */
    public function filter($collection)
    {
        // Ищем рекурсивную связь модели
        $recursiveRelationship = $this->recursiveRelationshipPropertyGetter->get($collection->getModel());

        // Если связь не найдена и модель не рекурсивна - возвращаем всю коллекцию
        if (is_null($recursiveRelationship)) {
            return $collection;
        }

        // Геттер для сущности по рекурсивной связи
        $recursiveRelationshipGetterName = 'get' . ucfirst($recursiveRelationship->getName());

        // Собираем ID всех элементов
        $ids = array();
        foreach ($collection as $item) {
            $ids[] = $item->getId();
        }

        // Находим все элементы, ID родителей которых отсутствуют в списке ID, эти элементы и являются корневыми
        $result = array();
        foreach ($collection as $item) {
            $related = $item->$recursiveRelationshipGetterName();
            if (!is_null($related)) {
                if (!in_array($related->getId(), $ids)) {
                    $result[] = $item;
                }
            } else {
                $result[] = $item;
            }
        }

        return $result;
    }
}