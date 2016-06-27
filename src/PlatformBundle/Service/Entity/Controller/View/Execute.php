<?php

namespace AppGear\PlatformBundle\Service\Entity\Controller\View;

use AppGear\PlatformBundle\Entity\Controller\View;
use Symfony\Component\HttpFoundation\Response;

class Execute
{
    /**
     * Запускает выполенение контроллера и возвращает результат
     *
     * @param View $controller Контроллер для отображения
     *
     * @return string
     */
    public function execute(View $controller)
    {
        return new Response($controller->getView()->render());
    }
}