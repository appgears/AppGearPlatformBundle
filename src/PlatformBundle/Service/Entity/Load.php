<?php

namespace AppGear\PlatformBundle\Service\Entity;

use AppGear\PlatformBundle\Entity\Model\Property\Field;
use AppGear\PlatformBundle\Entity\Model\Property\Relationship;
use AppGear\PlatformBundle\Factory\ModelFactory;
use Symfony\Component\Form\Exception\RuntimeException;

class Load
{
    /**
     * Model Factory
     *
     * @var Factory
     */
    protected $modelFactory;

    /**
     * @param Factory $modelFactory Model Factory
     */
    public function __construct(ModelFactory $modelFactory)
    {
        $this->modelFactory = $modelFactory;
    }

    /**
     * Загружает объект модели и связанные с ним объекты по переданным данным
     *
     * @param Model $model Модель
     * @param array $data Данные
     *
     * @throws RuntimeException
     *
     * @return object Инстанц модели
     */
    public function load($model, $data)
    {
        // Если передан идентификатор - пытаемся загрузить инстанц модели по нему
        if (array_key_exists('id', $data) && !empty($data['id'])) {

            $condition = new Condition();
            $condition->setContainer($this->container);
            $condition
                ->setField('id')
                ->setOperator('equal')
                ->setValue($data['id']);

            $filter = new Filter();
            $filter->setContainer($this->container);
            $filter->addItemToConditions($condition);

            $collection = new Collection();
            $collection->setContainer($this->container);
            $collection->setModel($model);
            $collection->setFilter($filter);

            $instance = null;
            foreach ($collection as $item) {
                $instance = $item;
                break;
            }

            if (is_null($instance)) {
                throw new RuntimeException('Не найден объект модели ' . $model->getName() . ' c идентификатором ' . $data['id']);
            }
        }

        // Если идентификатора нет - создаем сущность и инициализируем ее данными из запроса
        else {
            $instance = $model->getInstance();
        }

        // Инициализируем свойства сущности
        foreach ($model->getAllProperties() as $property) {

            if (!array_key_exists($property->getName(), $data)) {
                continue;
            }

            if ($property instanceof Field) {

                $setterMethodName = 'set' . ucfirst($property->getName());
                $instance->$setterMethodName($data[$property->getName()]);
            }
            elseif ($property instanceof Relationship) {

                // Имя параметра в котором может быть передано полное или относительное имя модели
                // (относительно связанной модели, если нужная модель наследуется от нее)
                // (Мы можем и так узнать имя связанной модели, но бывают ситуации, когда связанная модель бывает базо-
                // вой, а использовать нужно наследованную от нее модель, которую мы заранее не знаем)
                $modelNameParameterName = 'modelName';

                // Получаем модель с которой установлена связь
                $relatedModel = $property->getTarget();

                // В зависимости от типа связи устанавливаем связи
                if (in_array($property->getType(), array('ManyToOne', 'OneToOne'))) {

                    // Если передан параметр с именем модели - получаем модель по нему
                    if (array_key_exists($modelNameParameterName, $data[$property->getName()])) {
                        $relatedModel = $this->modelFactory->getModelByFullName($data[$property->getName()][$modelNameParameterName]);

                        if ($relatedModel === null) {
                            throw new \RuntimeException('Can\'t found model: ' . $data[$property->getName()][$modelNameParameterName]);
                        }
                    }

                    // Пытаемся получить инстанц связанной сущности
                    $relatedInstance = $this->load($relatedModel, $data[$property->getName()]);

                    if (!is_null($relatedInstance)) {
                        $setterMethodName = 'set' . ucfirst($property->getName());
                        $instance->$setterMethodName($relatedInstance);
                    }
                } else {

                    foreach ($data[$property->getName()] as $dataItem) {

                        // Если передан параметр с именем модели - получаем модель по нему
                        if (array_key_exists($modelNameParameterName, $dataItem)) {
                            $relatedModel = $this->modelFactory->getModelByFullName($dataItem[$modelNameParameterName]);

                            if ($relatedModel === null) {
                                throw new \RuntimeException('Can\'t found model: ' . $dataItem[$modelNameParameterName]);
                            }
                        }

                        // Пытаемся получить инстанц связанной сущности
                        $relatedInstance = $this->load($relatedModel, $dataItem);

                        if (!is_null($relatedInstance)) {
                            $setterMethodName = 'addItemTo' . ucfirst($property->getName());
                            $instance->$setterMethodName($relatedInstance);
                        }
                    }
                }
            }
        }

        return $instance;
    }
}