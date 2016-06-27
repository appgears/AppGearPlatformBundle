<?php

namespace AppGear\PlatformBundle\Service\Entity\View;

use AppGear\PlatformBundle\Entity\View;
use AppGear\PlatformBundle\Storage\Storage;
use Cosmologist\Gears\Object\PropertyRecursiveAccess;
use Symfony\Component\Templating\EngineInterface;

class Render
{
    /**
     * Template engine
     *
     * @var EngineInterface
     */
    protected $templateEngine;

    /**
     * Storage
     *
     * @var Storage
     */
    protected $storage;

    /**
     * Template finder
     *
     * @var FindTemplate
     */
    protected $templateFinder;

    /**
     * Конструктор
     *
     * @param Storage $storage Storage
     * @param EngineInterface $templateEngine Template engine
     * @param FindTemplate $templateFinder Template finder
     */
    public function __construct(Storage $storage, EngineInterface $templateEngine, FindTemplate $templateFinder)
    {
        $this->storage = $storage;
        $this->templateEngine = $templateEngine;
        $this->templateFinder = $templateFinder;
    }


    /**
     * Инициализирует данные передаваемые в шаблон
     *
     * @param array $data Prepared data
     *
     * @return array
     */
    protected function initData(array $data = [])
    {
        $models = $this->storage->find('AppGear\\PlatformBundle\\Entity\\Model', ['fullName' => get_class($data['entity'])]);

        $data['model'] = array_pop($models);
        $data['routePrefix'] = $data['view']->getRoutePrefix();

        return $data;
    }


    /**
     * Return existing template for the current view and entity
     *
     * @param View $view View
     *
     * @return string
     */
    protected function getTemplate($view)
    {
        $viewModel = $this->storage->findOne('AppGear\\PlatformBundle\\Entity\\Model', ['fullName' => get_class($view)]);

        if ($template = $this->templateFinder->find($this->templateEngine, $viewModel)) {
            return $template;
        }

        throw new \RuntimeException(sprintf('Template not found for view "%s"', $viewModel->getFullName()));
    }


    /**
     * Рендерит отображение и возвращает результат
     *
     * @param View $view Отображение
     * @param object $entity Если передана сущность, то рендер происходит для нее, если не передана, то рендер
     *                            выполняется для связанной сущности
     *
     * @return string
     */
    public function render(View $view, $entity = null)
    {
        if ($entity !== null) {
            $view->setEntity($entity);
        }

        $data = [
            'entity' => $view->getEntity(),
            'view'   => $view
        ];
        $data     = $this->initData($data);
        $template = $view->getTemplate() ?: $this->getTemplate($view);

        return $this->templateEngine->render($template, $data);
    }
}