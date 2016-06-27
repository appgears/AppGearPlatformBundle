<?php

namespace AppGear\PlatformBundle\Service\Entity\View\Entity\Edit;

use AppGear\PlatformBundle\Entity\View;

class Render extends \AppGear\PlatformBundle\Service\Entity\View\Render
{
    /**
     * {@inheritdoc}
     */
    protected function initData(array $data = [])
    {
        $data = parent::initData($data);

        $data['relatedModelsItems'] = $data['relatedModelsViews'] = [];

        foreach ($data['model']->getAllRelationships() as $relationship) {
            if ($target = $relationship->getTarget()) {

                $relatedModelFullName = $target->getFullName();

                // Подгружаем элементы связанной модели
                $data['relatedModelsItems'][$relatedModelFullName] = $this->storage->find($relatedModelFullName);

                // Ищем кастомное представление для отображения элементов связанной модели
                if ($template = $this->templateFinder->find($this->templateEngine, $target, ['Property', 'Edit'], ['Entity'])) {
                    $data['relatedModelsViews'][$relatedModelFullName] = $template;
                }
            }
        }

        return $data;
    }
}