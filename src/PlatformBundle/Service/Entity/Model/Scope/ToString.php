<?php

namespace AppGear\PlatformBundle\Service\Entity\Model\Scope;

class ToString
{
    /**
     * Атом для получения элементов пути к текущему неймспейсу
     *
     * @var GetParentOrSelfNames
     */
    protected $getParentOrSelfNamesAtom;

    /**
     * @param GetParentOrSelfNames $getParentOrSelfNamesAtom Атом для получения элементов пути к текущему неймспейсу
     */
    public function __construct(GetParentOrSelfNames $getParentOrSelfNamesAtom)
    {
        $this->getParentOrSelfNamesAtom = $getParentOrSelfNamesAtom;
    }

    /**
     * Full scope name
     *
     * @param Scope $scope Неймспейс
     *
     * @return string
     */
    public function to($scope)
    {
        return implode('\\', $this->getParentOrSelfNamesAtom->get($scope));
    }
}