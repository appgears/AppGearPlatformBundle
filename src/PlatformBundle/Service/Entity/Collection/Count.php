<?php

namespace AppGear\PlatformBundle\Service\Entity\Collection;

use AppGear\PlatformBundle\Entity\Collection;
use AppGear\PlatformBundle\Storage\Storage;

class Count
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
     * Возвращает количество сущностей в коллекции
     *
     * @param Collection $collection
     * @return \ArrayIterator
     */
    public function count(Collection $collection)
    {
        $conditions = [];
        if ($collection->getFilter() !== null) {
            foreach ($collection->getFilter()->getConditions() as $condition) {
                $conditions[$condition->getField()] = $condition->getValue();
            }
        }

        if (!($model = $collection->getModel())) {
            return 0;
        }

        return $this->storage->count($model->getFullName(), $conditions);
    }
}