<?php

namespace AppGear\PlatformBundle\Factory;

use AppGear\PlatformBundle\Entity\Model;
use AppGear\PlatformBundle\Entity\Storage;
use AppGear\PlatformBundle\Service\Standard\ObjectType\MapArray;
use Doctrine\DBAL\Connection;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class ModelFactory используется для получения экземпляров модели
 */
class ModelFactory
{
    /**
     * Connection
     *
     * @var Connection
     */
    protected $connection;

    /**
     * Container
     *
     * @var ContainerInterface
     */
    protected $container;

    /**
     * Экземпляр трансформации для наложения данных на объект
     *
     * @var Mapper
     */
    protected $mapper;

    protected $cache = [];

    /**
     * Конструктор
     *
     * @param Connection         $connection Connection
     * @param ContainerInterface $container  Container
     */
    public function __construct(Connection $connection, ContainerInterface $container, MapArray $mapper)
    {
        $this->connection = $connection;
        $this->container  = $container;
        $this->mapper     = $mapper;
    }


    /**
     * Возвращает модель по ее идентификатору
     *
     * @param integer $id ID модели
     *
     * @return Model
     */
    public function getModelById($id)
    {
        if (isset($this->cache[$id])) {
            return clone $this->cache[$id];
        }

        $modelData = $this->getModelDataById($id);

        $model = $this->buildEntity('AppGear\\PlatformBundle\\Entity\\Model', $modelData);

        $scope = $this->getEntity(6, $modelData['scope_id']);
        $model->setScope($scope);

        $storage = $this->getEntity(110, $modelData['storage_id']);
        $model->setStorage($storage);

        $parentId = $modelData['parent_id'];
        if (!empty($parentId)) {
            $parent = $this->getModelById($modelData['parent_id']);
            $model->setParent($parent);
        }

        $properties = $this->getEntities(7, 'main.model_id', $id);
        $model->setProperties($properties);

        $this->cache[$id] = $model;

        return $model;
    }


    protected function getAllModelsData()
    {
        return $this->connection->fetchAll('SELECT * FROM AppGearPlatformModel');
    }


    protected function getModelsData($field, $value)
    {
        $result = [];

        foreach ($this->getAllModelsData() as $model) {
            if ($model[$field] == $value) {
                $result[] = $model;
            }
        }

        return $result;
    }


    /**
     * Возвращает данные модели по ее идентификатору
     *
     * @param int $id ID Модели
     *
     * @return array
     */
    protected function getModelDataById($id = null)
    {
        $data = $this->getModelsData('id', $id);

        if (count($data)) {
            return $data[0];
        }

        return null;
    }


    protected function getLastModelData()
    {
        $models = $this->getAllModelsData();

        $lastModels = [];

        foreach ($models as $item1) {

            if (empty($item1['parent_id'])) {
                continue;
            }

            $haveParentItem = false;
            foreach ($models as $item2) {
                if ($item1['id'] == $item2['parent_id']) {
                    $haveParentItem = true;
                    break;
                }
            }

            if (!$haveParentItem) {
                $lastModels[] = $item1;
            }
        }

        return $lastModels;
    }


    /**
     * Возвращает список возможных комбинаций наследований моделей
     *
     * @param int $id ID базовой модели
     *
     * @return array Список комбинаций моделей
     */
    protected function getBranchedModelsData($id)
    {
        $result = [];

        // Ищем все конечные модели и пары связей
        $lastModels = $this->getLastModelData();

        // Строим списки комбинаций, поднимаясь от конечных моделей к корневым
        foreach ($lastModels as $lastModel) {
            $item = [$lastModel];

            $branchContainsPassedId = ($lastModel['id'] == $id);

            $currentModel = $lastModel;
            while (!empty($currentModel['parent_id']) && $currentModel = $this->getModelDataById($currentModel['parent_id'])) {
                if ($currentModel['id'] == $id) {
                    $branchContainsPassedId = true;
                }
                array_unshift($item, $currentModel);
            }

            if (!$branchContainsPassedId) {
                continue;
            }

            $result[] = $item;
        }

        return $result;
    }


    protected function getEntities($modelId, $field, $value)
    {
        $result = [];

        $branchedModelsData = $this->getBranchedModelsData($modelId);

        foreach ($branchedModelsData as $branchedModelData) {

            $query = $this->buildEntitiesQuery($branchedModelData);

            if (is_array($value)) {
                $query .= ' WHERE ' . $field . ' IN (?)';
                $queryOptions = [\Doctrine\DBAL\Connection::PARAM_INT_ARRAY];
            } else {
                $query .= ' WHERE ' . $field . ' = ?';
                $queryOptions = [];
            }

            if ($data = $this->connection->fetchAll($query, [$value], $queryOptions)) {
                foreach ($data as $row) {
                    $result[] = $this->buildEntity(end($branchedModelData)['fullName'], $row);
                }
            }
        }

        return $result;
    }


    protected function getEntity($modelId, $id)
    {
        $entities = $this->getEntities($modelId, 'main.id', $id);

        if (count($entities)) {
            return $entities[0];
        }

        return null;
    }


    protected function buildEntitiesQuery($branchedModelData)
    {
        $query = null;
        foreach ($branchedModelData as $modelData) {
            $currentTable = $this->getTableNameByModelFullName($modelData['fullName']);

            if ($currentTable === 'AppGearPlatformAppGear') {
                continue;
            }

            if ($query === null) {
                $query = 'SELECT * FROM ' . $currentTable . ' main ';
            } else {
                $query .= ' JOIN ' . $currentTable . ' ON ' . $currentTable . '.id = ' . 'main.id ';
            }
        }

        return $query;
    }


    protected function buildEntity($className, $data)
    {
        $instance = new $className();
        if (method_exists($instance, 'setContainer')) {
            $instance->setContainer($this->container);
        }
        $instance = $this->mapper->map($instance, $data);

        return $instance;
    }


    protected function getTableNameByModelFullName($fullName)
    {
        $tableName = str_replace('Bundle\\Model\\', '\\', $fullName);
        $tableName = str_replace('\\', '', $tableName);

        return $tableName;
    }
}