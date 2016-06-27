<?php

namespace AppGear\PlatformBundle\Storage\Mysql;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;

class Storage implements \AppGear\PlatformBundle\Storage\Storage
{
    /**
     * Container
     *
     * @var ContainerInterface
     */
    protected $container;

    /**
     * Storage driver
     *
     * @var Driver
     */
    protected $driver;

    /**
     * Normalizer
     *
     * @var GetSetMethodNormalizer
     */
    protected $normalizer;

    /**
     * Foreign keys cache
     *
     * @var array
     */
    protected $foreignKeys;

    /**
     * Constructor
     *
     * @param ContainerInterface     $container  Container
     * @param Driver                 $driver     Storage driver
     * @param GetSetMethodNormalizer $normalizer Normalizer
     */
    public function __construct(ContainerInterface $container, Driver $driver, GetSetMethodNormalizer $normalizer)
    {
        $this->container  = $container;
        $this->driver     = $driver;
        $this->normalizer = $normalizer;
    }


    /**
     * Instance entity and init it with the data
     *
     * @param array  $data                 Data
     * @param string $defaultDiscriminator If data does not contains discriminator the use default discriminator
     *
     * @return object
     */
    protected function initializer($data, $defaultDiscriminator)
    {
        if (array_key_exists('_discriminator', $data)) {
            $discriminator = $data['_discriminator'];
        } else {
            $discriminator = $defaultDiscriminator;
        }

        $instance = $this->normalizer->denormalize($data, $discriminator);
        if (method_exists($instance, 'setContainer')) {
            $instance->setContainer($this->container);
        }

        return $instance;
    }


    /**
     * Finds an entity by conditions.
     *
     * @param string   $model      The model name
     * @param array    $conditions The find conditions
     * @param array    $orders     Orders
     * @param int|null $limit      Limit result set
     * @param int|null $offset     Offset in the result set
     *
     * @return \object[]
     */
    public function find($model, array $conditions = [], array $orders = [], $limit = null, $offset = null)
    {
        $result = [];
        foreach ($this->driver->find($this->buildTableName($model), true, $conditions, $orders, $limit, $offset) as $item) {
            $result[] = $this->initializer($item, $model);
        }

        return $result;
    }

    /**
     * Finds a single entity by conditions.
     *
     * @param string   $model      The model name
     * @param array    $conditions The find conditions
     * @param array    $orders     Orders
     * @param int|null $offset     Offset in the result set
     *
     * @return object[]
     */
    public function findOne($model, array $conditions = [], array $orders = [], $offset = null)
    {
        $entities = $this->find($model, $conditions, $orders, 1, $offset);
        return array_shift($entities);
    }


    /**
     * Finds an entity by its identifier.
     *
     * @param string $model The model name
     *
     * @return object The entity instance
     */
    public function findById($model, $id)
    {
        return $this->initializer($this->driver->findById($this->buildTableName($model), $id), $model);
    }


    /**
     * Finds the related entities
     *
     * @param string  $fromModel Find related from this model
     * @param string  $toModel Find related to this model
     * @param integer $fromId Find related from model instance with this ID
     * @param string  $fromKey Relationship key
     *
     * @return object[] Related entities instances
     */
    public function findRelated($fromModel, $toModel, $fromId, $fromKey)
    {
        $result = [];

        $fromTable = $this->buildTableName($fromModel);
        $toTable   = $this->buildTableName($toModel);
        $items = $this->driver->findRelated($fromTable, $toTable, $fromId, $fromKey);

        foreach ($items as $item) {
            $result[] = $this->initializer($item, $item['_discriminator']);
        }

        return $result;
    }


    /**
     * Count of entities by conditions
     *
     * @param string $model The model name
     * @param array $conditions Conditions
     *
     * @return int
     */
    public function count($model, array $conditions = [])
    {
        return $this->driver->count($this->buildTableName($model), $conditions);
    }


    /**
     * Save the model entity to the database
     *
     *
     * @param string $model The model name
     * @param object $entity The entity
     *
     * @throws \RuntimeException
     *
     * @return object The entity
     */
    public function save($model, $entity)
    {
        $table = $this->buildTableName($model);

        $data = $this->normalize($entity);

        // Save entity data
        $id = $this->driver->save($table, $data['data'], $model);
        $entity->setId($id);

        // Save related entities data
        foreach ($data['related'] as $key => $related) {
            $this->driver->relate($table, $this->buildTableName($related['class']), $id, $related['value'], $key);
        }

        return $entity;
    }


    /**
     * Remove the entity from database
     *
     *
     * @param string $model The model name
     * @param object $entity The entity
     */
    public function remove($model, $entity)
    {
        $this->driver->remove($this->buildTableName($model), $entity->getId());
    }


    /**
     * Get data from object for saving to the storage
     *
     * @param $object
     *
     * @return array
     */
    protected function normalize($object)
    {
        $reflectionObject = new \ReflectionObject($object);
        $reflectionMethods = $reflectionObject->getMethods(\ReflectionMethod::IS_PUBLIC);

        $result = [
            'data' => [],
            'related' => []
        ];
        foreach ($reflectionMethods as $method) {
            if ($this->isGetMethod($method)) {

                $attributeName = lcfirst(substr($method->name, 0 === strpos($method->name, 'is') ? 2 : 3));
                $attributeValue = $method->invoke($object);

                if (is_object($attributeValue)) {

                    if (method_exists($attributeValue, 'getId')) {
                        $result['related'][$attributeName] = [
                            'class' => get_class($attributeValue),
                            'value' => $attributeValue->getId()
                        ];
                    }
                } elseif (is_array($attributeValue)) {

                    foreach ($attributeValue as $attributeValueItem) {
                        if (is_object($attributeValueItem) && method_exists($attributeValueItem, 'getId')) {
                            $id = $attributeValueItem->getId();

                            if (!array_key_exists($attributeName, $result['related'])) {
                                $result['related'][$attributeName] = [
                                    'class' => get_class($attributeValueItem),
                                    'value' => []
                                ];
                            }

                            $result['related'][$attributeName]['value'][] = $id;
                        }
                    }
                } else {
                    $result['data'][$attributeName] = $attributeValue;
                }
            }
        }

        return $result;
    }


    /**
     * Checks if a method's name is get.* or is.*, and can be called without parameters.
     *
     * @param \ReflectionMethod $method the method to check
     *
     * @return bool whether the method is a getter or boolean getter.
     */
    private function isGetMethod(\ReflectionMethod $method)
    {
        $methodLength = strlen($method->name);

        return (
            ((0 === strpos($method->name, 'get') && 3 < $methodLength) ||
                (0 === strpos($method->name, 'is') && 2 < $methodLength)) &&
            0 === $method->getNumberOfRequiredParameters()
        );
    }


    /**
     * Build table name
     *
     * @param string $model The model name
     *
     * @return string
     */
    public function buildTableName($model)
    {
        $tableName = str_replace('Bundle\\Entity\\', '\\', $model);
        $tableName = str_replace('\\', '', $tableName);

        return $tableName;
    }
}