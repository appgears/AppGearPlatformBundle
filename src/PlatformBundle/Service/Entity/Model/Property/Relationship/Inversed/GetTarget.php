<?php

namespace AppGear\PlatformBundle\Service\Entity\Model\Property\Relationship\Inversed;

class GetTarget
{
    /**
     * Возвращает модель с которой установлена связь
     *
     * @param Inversed $property
     * @throws \RuntimeException
     *
     * @return Relationship
     */
    public function get($property)
    {
        return $property->getOpposite()->getModel();
    }
}