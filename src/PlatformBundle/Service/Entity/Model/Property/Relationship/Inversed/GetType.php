<?php

namespace AppGear\PlatformBundle\Service\Entity\Model\Property\Relationship\Inversed;

class GetType
{
    /**
     * Возвращает тип связи
     *
     * @param Inversed $property
     * @throws \RuntimeException
     *
     * @return string
     */
    public function get($property)
    {
        $sides = array(
            'ManyToOne'  => 'OneToMany',
            'OneToMany'  => 'ManyToOne',
            'ManyToMany' => 'ManyToMany',
            'OneToOne'   => 'OneToOne',
        );

        return $sides[$property->getOpposite()->getType()];
    }
}