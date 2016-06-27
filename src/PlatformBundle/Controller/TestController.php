<?php

namespace AppGear\PlatformBundle\Controller;

use AppGear\PlatformBundle\Entity\Model\Property\Field;
use AppGear\PlatformBundle\Entity\Model\Property\Relationship;
use AppGear\PlatformBundle\Entity\Collection;
use AppGear\PlatformBundle\Entity\Collection\Filter\Condition;
use AppGear\PlatformBundle\Entity\Collection\Filter;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class TestController extends Controller
{
    public function storageAction(Request $request)
    {
        $storage = $this->container->get('ag.storage.postgresql');

        $entity = $storage->find('AppGear\\PlatformBundle\\Entity\\Model', 2);

        var_dump($entity->getName());
        echo '<br>';

        exit;
    }

    public function testAction(Request $request)
    {
        $factory = $this->container->get('ag.factory.model_factory');

        $model = $factory->getModelById(101);

        echo $model->getId(), '<br>';
        echo $model->getName(), '<br>';
        foreach ($model->getAllProperties() as $property) {
            echo $property->getId(), '<br>';
            echo $property->getName(), '<br>';
        }
//        echo '<br>';
//        echo $model->getStorage()->getId(), '<br>';
//        echo $model->getStorage()->getName(), '<br>';
//        echo '<br>';
//        echo $model->getParent()->getId(), '<br>';
//        echo $model->getParent()->getName(), '<br>';
//        echo '<br>';
//        echo $model->getParent()->getParent()->getId(), '<br>';
//        echo $model->getParent()->getParent()->getName(), '<br>';
//        echo '<br>';
//        foreach ($model->getParent()->getParent()->getProperties() as $property) {
//            echo $property->getId(), '<br>';
//            echo $property->getName(), '<br>';
//        }
        exit;
    }
}
