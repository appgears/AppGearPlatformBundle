<?php

namespace AppGear\PlatformBundle\Service\Entity\Model;

use Symfony\Component\DependencyInjection\ContainerInterface;

class GetInstance
{
    /**
     * @var ContainerInterface Сервис-контейнер
     */
    protected $container;

    /**
     * @var object Атом для генерации исходного кода модели
     */
    protected $generateSourceAtom;

    /**
     * @var object Атом для получения пути к файлу с исходным кодом модели
     */
    protected $getSourcePathAtom;

    /**
     * @param ContainerInterface $container Сервис-контейнер
     * @param object $generateSourceAtom Атом для генерации исходного кода модели
     * @param object $getSourcePathAtom Атом для получения пути к файлу с исходным кодом модели
     */
    public function __construct(ContainerInterface $container, $generateSourceAtom, $getSourcePathAtom)
    {
        $this->container = $container;
        $this->generateSourceAtom = $generateSourceAtom;
        $this->getSourcePathAtom = $getSourcePathAtom;
    }

    /**
     * Возвращает экземпляр модели
     *
     * @param $model Модель
     * @return object
     */
    public function get($model)
    {
        // Путь к файлу класса модели
        $path = $this->getSourcePathAtom->get($model);

        // Если файл класса модели не найден
        if (!file_exists($path)) {

            // Запускаем генератор
            $this->generateSourceAtom->generate($model);
        }

        // Инстанцируем класс модели
        $class_name = $model->getFullName();
        $instance = new $class_name;

        // Даем доступ к сервис-контейнеру
        if (method_exists($instance, 'setContainer')) {
            $instance->setContainer($this->container);
        }

        return $instance;
    }
}