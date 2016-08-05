<?php

namespace AppGear\PlatformBundle\Service\Entity\View\Container\Admin;

use AppGear\PlatformBundle\Entity\View;
use AppGear\PlatformBundle\Service\Entity\View\Container\Render as ContainerRender;
use Cosmologist\Gears\ObjectType\PropertyRecursiveAccess;

class Render extends ContainerRender
{
    /**
     * {@inheritdoc}
     */
    protected function initData(array $data = [])
    {
        $data = parent::initData($data);

        $data['scopes'] = [
            'appgear' => null,
            'other'   => []
        ];

        // Prepare scopes for left side menu
        $rootScope = $this->storage->findOne('AppGear\\PlatformBundle\\Entity\\Model\\Scope', ['parent_id' => null]);
        foreach ($rootScope->getChildren() as $scope) {
            if ($scope->getName() === 'AppGear') {
                $data['scopes']['appgear'] = $scope;
            } else {
                $data['scopes']['other'][] = $scope;
            }
        }

        // Find current model
        $selectedModel = null;
        $entity        = $data['view'];
        while ($entity = $entity->getEntity()) {
            if ($entity instanceof View\Collection && ($collectionEntity = $entity->getEntity())) {
                $selectedModel = $collectionEntity->getModel();
                break;
            } elseif (!($entity instanceof View)) {
                $selectedModel = $this->storage->findOne('AppGear\\PlatformBundle\\Entity\\Model', ['fullName' => get_class($entity)]);
                break;
            }
        }

        $data['selected'] = [
            'model'  => $selectedModel,
            'scopes' => []
        ];

        if ($selectedModel !== null) {
            $modelScope   = $selectedModel->getScope();
            $data['selected']['scopes'] = PropertyRecursiveAccess::get($modelScope, 'parent');
            array_unshift($data['selected']['scopes'], $modelScope);
            $data['selected']['scopes'] = array_reverse($data['selected']['scopes']);

            // Remove root empty scope
            array_shift($data['selected']['scopes']);
        }

        return $data;
    }
}