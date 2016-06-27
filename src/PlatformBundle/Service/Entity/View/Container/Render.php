<?php

namespace AppGear\PlatformBundle\Service\Entity\View\Container;

use AppGear\PlatformBundle\Entity\Collection\Filter;
use AppGear\PlatformBundle\Entity\View;
use AppGear\PlatformBundle\Service\Entity\View\Render as ViewRender;

class Render extends ViewRender
{
    /**
     * {@inheritdoc}
     */
    protected function initData(array $data = [])
    {
        $data = parent::initData($data);

        $currentRoutePrefix = $data['view']->getRoutePrefix();
        $nextRoutePrefix    = $currentRoutePrefix . 'entity_';

        $data['container'] = null;
        if ($entity = $data['entity']) {
            $data['container'] = $entity->setRoutePrefix($nextRoutePrefix)->render();
        }

        return $data;
    }
}