<?php

namespace AppGear\PlatformBundle\Service\Entity\Controller\Remove;

use AppGear\PlatformBundle\Storage\Storage;
use Symfony\Component\HttpFoundation\Response;
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
     * @param RouterInterface $router  Router
     * @param Storage         $storage Storage
     */
    public function __construct(RouterInterface $router, Storage $storage)
    {
        $this->router  = $router;
        $this->storage = $storage;
    }

    /**
     * Remove the entity from the storage
     *
     * @param SystemController $controller Controller
     *
     * @return string
     */
    public function execute($controller)
    {
        $entity = $controller->getEntity();

        $model = $this->storage->find('AppGear\\PlatformBundle\\Entity\\Model', ['fullName' => get_class($entity)])[0];

        $this->storage->remove($model->getFullName(), $entity);

        return new Response('');
    }
}