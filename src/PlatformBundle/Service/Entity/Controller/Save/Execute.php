<?php

namespace AppGear\PlatformBundle\Service\Entity\Controller\Save;

use AppGear\PlatformBundle\Storage\Storage;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\RouterInterface;

class Execute
{
    /**
     * Router
     *
     * @var RouterInterface
     */
    protected $router;

    /**
     * Хранилише
     *
     * @var Storage
     */
    protected $storage;

    /**
     * @param RouterInterface $router Router
     * @param Storage $storage Storage
     */
    public function __construct(RouterInterface $router, Storage $storage)
    {
        $this->router = $router;
        $this->storage   = $storage;
    }

    /**
     * Save entity to the storage
     *
     * @param SystemController $controller Controller
     *
     * @return RedirectResponse
     */
    public function execute($controller)
    {
        $entity = $controller->getEntity();

        $model = $this->storage->find('AppGear\\PlatformBundle\\Entity\\Model', ['fullName' => get_class($entity)])[0];

        $entity = $this->storage->save($model->getFullName(), $entity);

        return new RedirectResponse($this->router->generate('appgear_view_admin_entity_detail_dynamic', ['view_entity_entity_modelId' => $model->getId(), 'view_entity_entity_id' => $entity->getId()]));
    }
}