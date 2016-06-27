<?php

namespace AppGear\PlatformBundle\Service\Entity\Model\Scope;

class GetParentOrSelfNames
{
    /**
     * Return names array of scope from first parent to this scope
     *
     * @param Scope $scope Namespace
     *
     * @return string
     */
    public function get($scope)
    {
        $names = array($scope->getName());
        while ($scope = $scope->getParent()) {
            array_unshift($names, $scope->getName());
        }

        // Ignore root namespace
        array_shift($names);

        return $names;
    }
}