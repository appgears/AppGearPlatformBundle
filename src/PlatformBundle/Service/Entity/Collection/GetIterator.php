<?php

namespace AppGear\PlatformBundle\Service\Entity\Collection;

use AppGear\PlatformBundle\Entity\Collection;
use AppGear\PlatformBundle\Storage\Storage;

class GetIterator
{
    /**
     * Storage
     *
     * @var Storage
     */
    protected $storage;

    /**
     * Constructor
     *
     * @param Storage $storage Storage
     */
    public function __construct(Storage $storage)
    {
        $this->storage = $storage;
    }

    /**
     * Реализует интерфейс итератора для коллекции
     *
     * @param Collection $collection
     *
     * @return \ArrayIterator
     */
    public function getIterator(Collection $collection)
    {
        $offset     = $limit = null;
        $conditions = $orders = [];

        if ($filter = $collection->getFilter()) {
            $offset = $collection->getFilter()->getOffset();
            $limit = $collection->getFilter()->getLimit();

            foreach ($collection->getFilter()->getConditions() as $condition) {
                $conditions[$condition->getField()] = $condition->getValue();
            }
            foreach ($collection->getFilter()->getOrders() as $order) {
                $orders[$order->getField()] = $order->getDirection();
            }
        }

        return new \ArrayIterator($this->storage->find($collection->getModel()->getFullName(), $conditions, $orders, $limit, $offset));
    }
}