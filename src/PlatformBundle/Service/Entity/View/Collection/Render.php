<?php

namespace AppGear\PlatformBundle\Service\Entity\View\Collection;

use AppGear\PlatformBundle\Entity\Collection\Filter;
use AppGear\PlatformBundle\Entity\View;

class Render extends \AppGear\PlatformBundle\Service\Entity\View\Render
{
    /**
     * {@inheritdoc}
     */
    protected function initData(array $data = [])
    {
        $data = parent::initData($data);

        // TODO: default limit value were be set in filter class
        $filter = $data['entity']->getFilter();
        if (!$filter) {
            $filter = new Filter();
            $data['entity']->setFilter($filter);
        }

        if (!($limit = $filter->getLimit())) {
            $limit = 25;
            $data['entity']->getFilter()->setLimit($limit);
        }

        $offset = ($data['view']->getCurrentPage() - 1) * $limit;
        if ($offset < 0) {
            $offset = 0;
        }

        $data['entity']->getFilter()->setOffset($offset);

        return $data;
    }
}