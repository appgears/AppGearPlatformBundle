<?php

namespace AppGear\PlatformBundle\Service\Entity\Model\Scope;

class GetFullName
{
    /**
     *  Возвращает полное имя
     *
     * @param Scope $scope
     *
     * @return string
     */
    public function get($scope)
    {
        return implode('\\', $scope->getParentOrSelfNames());
    }
}