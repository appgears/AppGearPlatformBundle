<?php

namespace AppGear\PlatformBundle\Factory;

use AppGear\PlatformBundle\Entity;
use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class Factory используется для получения экземпляров модели
 */
class Factory
{
    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var Container
     */
    protected $container;

    /**
     * @param EntityManager $entityManager
     * @param ContainerInterface $container
     */
    public function __construct(EntityManager $entityManager, ContainerInterface $container)
    {
        $this->entityManager = $entityManager;
        $this->container = $container;
    }

    /**
     * Возвращает модель по ее полному имени
     *
     * @param string $name Имя модели
     *
     * @return Entity\Model
     */
    public function getModelByFullName($name)
    {
        return $this->entityManager->getRepository('AppGearPlatformBundle:Model')->findOneBy(array('fullName' => $name));
    }

    /**
     * Возвращает модель по ее инстанцу
     *
     * @param AppGear $instance Инстанц модели
     *
     * @return Entity\Model
     */
    public function getModelByInstance($instance)
    {
        return $this->getModelByFullName(get_class($instance));
    }

    /**
     * Возвращает модель по ее идентификатору
     *
     * @param integer $id ID модели
     *
     * @return Entity\Model
     */
    public function getModelById($id)
    {
        return $this->entityManager->getRepository('AppGearPlatformBundle:Model')->find($id);
    }
}