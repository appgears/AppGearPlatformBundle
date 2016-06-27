<?php

namespace AppGear\PlatformBundle\Service\Entity\Model;

use AppGear\PlatformBundle\Entity\Model\Property\Field;

class GetAllFields
{
    protected $allPropertiesGetter;

    public function __construct($allPropertiesGetter)
    {
        $this->allPropertiesGetter = $allPropertiesGetter;
    }

    /**
     * Возвращает все свойства связи модели, а также все свойства связи родительских моделей
     *
     * @param Model $model
     * @return array
     */
    public function get($model)
    {
        $result = array();

        foreach ($this->allPropertiesGetter->get($model) as $property) {
            if ($property instanceof Field) {
                $result[] = $property;
            }
        }

        return $result;
    }
}