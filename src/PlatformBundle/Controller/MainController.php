<?php

namespace AppGear\PlatformBundle\Controller;

use AppGear\PlatformBundle\Entity\Collection;
use AppGear\PlatformBundle\Entity\Collection\Filter;
use AppGear\PlatformBundle\Entity\Model;
use AppGear\PlatformBundle\Entity\Model\Property\Field;
use AppGear\PlatformBundle\Entity\Model\Property\Relationship;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class MainController extends Controller
{
    /**
     * Разделитель используемый в именах параметров
     */
    const PARAMETER_NAME_SEPARATOR = '_';

    /**
     * Точка входа в контроллер AppGear
     *
     * Контроллеры AppGear не используются напрямую, так как они реализованы в объектно-ориентрированном стиле.
     * Вместо представления каждого action в Symfony в виде отдельного метода, в AppGear каждый action реализуется в
     * виде отдельного объекта, и в контексте AppGear именно action называется контроллером.
     *
     * @param Request $request Объект запроса
     * @param string $controllerModelId ID модели контроллера
     *
     * @throws RuntimeException
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function entryPointAction(Request $request, $controllerModelId)
    {
        // Получаем модель контроллера
        $controllerModel = $this
            ->get('ag.storage')
            ->findById('AppGear\\PlatformBundle\\Entity\\Model', $controllerModelId);

        if (is_null($controllerModel)) {
            throw new RuntimeException('Неизвестный контроллер: ' . $controllerModelId);
        }

        $parameters = array_merge($request->attributes->all(), $request->request->all(), $request->query->all(), $request->files->all());

        // Инициализируем контроллер используя данные из запроса
        $controller = $this->init($request, null, $controllerModel, $parameters);

        // Выполняем контроллер и отдаем результаты выполнения
        return $controller->execute();
    }


    /**
     * Создает инстанц модели на основе данных из запроса
     *
     * @param Request $request Объект запроса
     * @param object $instance Инстанц модели
     * @param object $model Модель
     * @param array $parameters Аттрибуты из запроса
     * @param string $prefix Префикс имен параметров сущности получаемых из аттрибутов запроса
     * @return mixed
     */
    private function init(Request $request, $instance, $model, $parameters, $prefix = '')
    {
        // Имя параметра в котором может быть передан идентификатор сущности
        $idParameterName = $this->buildParameterName('id', $prefix);

        // Если передан идентификатор - пытаемся загрузить инстанц модели по нему
        if (array_key_exists($idParameterName, $parameters) && !empty($parameters[$idParameterName])) {
            $instance = $this
                ->get('ag.storage')
                ->findById($model->getFullName(), $parameters[$idParameterName]);

            // Если имя переданной модели не совпадает с именем класса инстанца,
            // значит передана одна из родительских моделей, получаем конкретную модуль по инстанцу
            if (get_class($instance) !== $model->getFullName()) {
                $model = $this
                    ->get('ag.storage')
                    ->findOne('AppGear\\PlatformBundle\\Entity\\Model', ['fullName' => get_class($instance)]);
            }
        }
        // Если идентификатора нет - создаем сущность и инициализируем ее данными из запроса
        elseif ($instance === null) {
            $instance = $model->getInstance();
        }


        // Инициализируем свойства сущности
        foreach ($model->getAllProperties() as $property) {

            if ($property instanceof Field) {

                // Игнорируем калькулируемые поля
                if (strlen($property->getServiceName()) > 0) {
                    continue;
                }

                // Имя параметра для данного поля в запросе
                $parameterName = $this->buildParameterName($property->getName(), $prefix);

                // Если имя передано - получаем значение параметра и проставляем инстанцу
                if (!array_key_exists($parameterName, $parameters)) {
                    continue;
                }

                // Значение параметра
                $parameterValue = $parameters[$parameterName];

                // Ищем подходящий сервис для инициализации данного типа свойства
                if ($suitableInitService = $this->findSuitableInitService($property)) {
                    $parameterValue = $suitableInitService->init($instance, $parameterValue, $property);
                }

                $setterMethodName = 'set' . ucfirst($property->getName());
                $instance->$setterMethodName($parameterValue);

            } elseif ($property instanceof Relationship) {

                // Префикс для параметров в запросе для связанной модели
                $parametersPrefix = $this->buildParameterName($property->getName(), $prefix);

                $currentParameters = $this->getParamsWithPrefix($parameters, $parametersPrefix);

                // Если с таким префиксом нет ни одного параметра - ничего не делаем
                if (count($currentParameters) === 0) {
                    continue;
                }

                // C текущим префиксом только один параметр
                $onlyOneParameter = count($currentParameters) === 1;
                // Первый параметр является параметром идентификатора
                $isIdParameter = key($currentParameters) === $this->buildParameterName('id', $parametersPrefix);
                // Значение первого параметра - пустое
                $firstParameter = current($currentParameters);
                $isEmptyIdParameter = empty($firstParameter);

                if ($onlyOneParameter && $isIdParameter && $isEmptyIdParameter) {
                    continue;
                }

                // Имя параметра в котором может быть передан ID модели связанной сущности
                // (Мы можем и так узнать имя связанной модели, но бывают ситуации, когда связанная модель бывает базо-
                // вой, а использовать нужно наследованную от нее модель, которую мы заранее не знаем)
                $modelIdParameterName = $this->buildParameterName('modelId', $parametersPrefix);

                // Если имя передано - получаем модель по нему
                if (array_key_exists($modelIdParameterName, $parameters) && !empty($parameters[$modelIdParameterName])) {
                    $relatedModel = $this
                        ->get('ag.storage')
                        ->findById('AppGear\\PlatformBundle\\Entity\\Model', $parameters[$modelIdParameterName]);
                } // Иначе получаем модель с которой установлена связь
                else {
                    $relatedModel = $property->getTarget();
                }

                $getterMethodName = 'get' . ucfirst($property->getName());
                $setterMethodName = 'set' . ucfirst($property->getName());

                if ($property->getType() === 'OneToMany' || $property->getType() === 'ManyToMany') {

                    // Получаем набор связанных инстанцов сущности по параметрам из запроса
                    $related = $this->initMany($request, $relatedModel, $currentParameters, $parametersPrefix);
                } else {

                    // Получаем связанную сущность
                    $related = $instance->$getterMethodName();

                    // Инициализируем или получаем инстанц связанной сущности по данным из запроса
                    $related = $this->init($request, $related, $relatedModel, $parameters, $parametersPrefix);
                }

                // Если получили - связываем с текущей сущностью
                if ($related !== null || $related !== []) {
                    $instance->$setterMethodName($related);
                }
            }
        }

        return $instance;
    }


    /**
     * Инициализирует набор сущностей по параметрам из запроса
     *
     * Сущности могут быть только инстацированы - модифицировать их нельзя,
     * поэтому обрабатывается только параметр содержащий набор идентификаторов сущностей
     *
     * @param Request $request Запрос
     * @param object $model Модель
     * @param array $parameters Аттрибуты из запроса
     * @param string $prefix Префикс имен параметров сущности получаемых из аттрибутов запроса
     *
     * @throws RuntimeException
     * @return null|array
     */
    private function initMany(Request $request, $model, $parameters, $prefix = '')
    {
        $result = [];

        // Набор связанных сущностей может приходить как:
        // entity => array(id => 1, id => 2, ...);
        // или как:
        // entity_id => array(1, 2, ...);
        //
        // Если у нас второй случай, то приводим его к формату первого
        if (!array_key_exists($prefix, $parameters) && array_key_exists($prefix . '_id', $parameters)) {
            $parameters[$prefix] = [];
            foreach ($parameters[$prefix . '_id'] as $parameter) {
                $parameters[$prefix][] = ['id' => $parameter];
            }
        }

        foreach ($parameters[$prefix] as $parameter) {
            $result[] = $this->init($request, null, $model, $parameter);
        }

        return $result;
    }


    /**
     * Ищет наиболее подходящий сервис для инициализации поля модели
     *
     * @param Field $field Поле
     *
     * @return null|object
     */
    private function findSuitableInitService(Field $field)
    {
        $fieldModel = $this
            ->get('ag.storage')
            ->findOne('AppGear\\PlatformBundle\\Entity\\Model', ['fullName' => get_class($field)]);

        $taggedManager = $this->container->get('ag.service.tagged_manager');

        while ($fieldModel !== null) {

            $services = $taggedManager
                ->findServices('ag.field.initializer', ['field' => strtolower($fieldModel->getName())]);

            if (count($services) === 1) {
                return $this->get(array_shift($services)['id']);
            }

            $fieldModel = $fieldModel->getParent();
        }

        return null;
    }


    /**
     * Возвращает все параметры с указанным префиксом
     *
     * @param array $params Параметры
     * @param string $prefix Префикс
     *
     * @return array
     */
    protected function getParamsWithPrefix($params, $prefix)
    {
        $result = [];

        foreach ($params as $key => $value) {
            $keyContainsPrefix = (strpos($key, $prefix . self::PARAMETER_NAME_SEPARATOR) !== false);
            $keyEqualsPrefix = ($prefix === $key);
            if ($keyContainsPrefix || $keyEqualsPrefix) {
                $result[$key] = $value;
            }
        }

        return $result;
    }

    /**
     * Собирает имя параметра на основе имени параметра и префикса
     *
     * @param string $name Имя параметра
     * @param string $prefix Префикс
     *
     * @return string
     */
    protected function buildParameterName($name, $prefix)
    {
        if ($prefix === '') {
            return $name;
        }

        return $prefix . self::PARAMETER_NAME_SEPARATOR . $name;
    }
}
