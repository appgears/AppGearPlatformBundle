<?php

namespace AppGear\PlatformBundle\Service\Entity\Model;

use AppGear\PlatformBundle\Entity\Model\Property\Field;
use AppGear\PlatformBundle\Entity\Model\Property\Field\Scalar\Boolean;
use AppGear\PlatformBundle\Entity\Model\Property\Field\Scalar\Integer;
use AppGear\PlatformBundle\Entity\Model\Property\Relationship;
use PhpParser;
use PhpParser\BuilderFactory;
use PhpParser\Node;
use PhpParser\Node\Stmt\UseUse;
use PhpParser\Parser;
use PhpParser\PrettyPrinter\Standard;
use Symfony\Component\DependencyInjection\ContainerInterface;

class GenerateSource
{
    /**
     * Сервис-контейнер
     *
     * @var ContainerInterface
     */
    protected $container;

    /**
     * Требуется ли сервис-контейнер для модели
     *
     * @var boolean
     */
    protected $needServiceContainer;

    /**
     * Контейнер уже инжектился (при генерации родительских моделей)
     *
     * @var boolean
     */
    protected $injectedServiceContainer;

    /**
     * Парсер PHP
     *
     * @var \PhpParser\Parser
     */
    protected $parser;

    /**
     * Фабрика для производства основных языковых конструкций
     *
     * @var \PhpParser\BuilderFactory
     */
    protected $factory;

    /**
     * Already generate models IDs
     * @var array
     */
    protected $alreadyGeneratedModelsIds = [];


    /**
     * Constructor
     *
     * @param ContainerInterface $container Container
     * @param Parser $parser PHP Parser
     * @param BuilderFactory $factory PHP Parser Builder Factory
     */
    public function __construct(ContainerInterface $container, Parser $parser, BuilderFactory $factory)
    {
        $this->container = $container;
        $this->parser = $parser;
        $this->factory = $factory;
    }

    /**
     * Генерирует исходники модели
     *
     * @param Model $model Модель
     *
     * @throws \RuntimeException
     *
     * @return void
     */
    public function generate($model)
    {
        // Проверяем что модель не генерировалась в текущей сессии (актуально при генерации родительских моделей)
        if (in_array($model->getId(), $this->alreadyGeneratedModelsIds)) {
            return true;
        }

        $this->injectedServiceContainer = false;

        $this->buildModel($model);
    }


    /**
     * Генерирует исходники модели
     *
     * @param Model $model Модель
     *
     * @throws \RuntimeException
     *
     * @return void
     */
    private function buildModel($model)
    {
        // Генерируем родительскую модель
        if ($parent = $model->getParent()) {
            $this->generate($parent);
        }

        $this->needServiceContainer = false;

        // Принтер для генерации исходного кода на основе его представления
        $prettyPrinter = new Standard;

        // Неймспейс
        $namespaceParts = $model->getScope()->getParentOrSelfNames();
        $namespaceNode = new Node\Stmt\Namespace_(new Node\Name($namespaceParts));

        // Если модель наследуется и неймспейс текущей модели не совпадает с неймспейсом родительской модели
        $useNode = null;
        if ($model->getParent() !== null) {
            $parentFullName = $model->getParent()->getFullName();
            $useNode = new Node\Stmt\Use_(array(new UseUse(new Node\Name($parentFullName))));
        }

        // Создаем класс для модели
        $classNode = $this->factory->class($model->getName());

        // Если модель наследуется от другой модели - добавляем extend
        if (($parent = $model->getParent())) {
            $classNode->extend(ucfirst($parent->getName()));
        }

        // Собираем свойства модели
        $this->buildProperties($model, $classNode);

        // Подключаем логику через атомы
        $this->buildLogic($model, $classNode);

        // Генерируем __toString
        $this->buildStringRepresentation($model, $classNode);

        // При необходимости добавляем к классу сервис-контейнер
        if ($this->needServiceContainer && !$this->injectedServiceContainer) {

            // Добавляем свойство для хранения сервис-контейнера
            $property_node = $this->factory->property('container')->makeProtected();
            $property_node = $property_node->getNode();
            $this->addDocComment($property_node, 'Container');
            $classNode->addStmt($property_node);

            // Сеттер для сервис-контейнера
            $setterNode = $this->factory->method('setContainer')->makePublic();
            $setterParam = $this->factory->param('container');
            $setterNode->addParam($setterParam);
            $code = '<?php $this->container = $container; return $this;';
            $setterNode->addStmts($this->parser->parse($code));
            $setterNode = $setterNode->getNode();
            $classNode->addStmt($setterNode);

            $this->injectedServiceContainer = true;
        }

        // Генерируем исходный код
        $sourceElements = array($namespaceNode);
        if (isset($useNode)) {
            $sourceElements[] = $useNode;
        }
        $sourceElements[] = $classNode->getNode();
        $sourceCode = $prettyPrinter->prettyPrintFile($sourceElements);

        // Сохраняем класс модели в файл
        $sourcePath = $this->container->get('ag.service.entity.model.get_source_path')->get($model);
        $this->saveSource($sourcePath, $sourceCode);

        $this->alreadyGeneratedModelsIds[] = $model->getId();
    }


    /**
     * Подключаем логику к классу через атомы
     *
     * @param Model $model Модель
     * @param Node $classNode Узел класса
     */
    private function buildProperties($model, $classNode)
    {
        foreach ($model->getProperties() as $property) {

            if ($property instanceof Field) {
                $this->buildPropertyField($property, $classNode);
            } elseif ($property instanceof Relationship) {
                $this->buildPropertyRelationship($property, $classNode);
            }
        }
    }

    /**
     * Подключаем поля к классу
     *
     * @param Field $field Поле
     * @param Node $classNode Узел класса
     */
    private function buildPropertyField($field, $classNode)
    {
        // Создаем свойство
        $builder = $this->factory->property($field->getName())->makeProtected();

        // Если надо устанавливаем значение по-умолчанию для свойства
        $this->addPropertyFieldDefaultValue($builder, $field);
        $defaultValue = $field->getDefaultValue();
        if (!is_null($defaultValue)) {

            // Так как значение по-умолчаню хранится как строка, его надо привести к типу поля
            if ($field instanceof Integer) {
                $value = (int)$defaultValue;
            } elseif ($field instanceof Boolean) {
                $value = (int)$defaultValue;
            } else {
                $value = $defaultValue;
            }
            $builder->setDefault($value);
        }

        $node = $builder->getNode();

        // Комментарий к свойству
        $this->addDocComment($node, ucfirst($field->getName()), 1);

        // Добавляем свойство к классу
        $classNode->addStmt($node);

        // Добавляем геттер
        $this->addFieldGetter($field, $classNode);

        // Сеттер
        // У калькулируемых атрибутов сеттера нет - значение всегда только считается
        if (strlen($field->getServiceName()) == 0) {
            $this->addPropertySetter($classNode, $field);
        }
    }


    /**
     * Подключаем связи к классу
     *
     * @param Relationship $relationship Связь
     * @param Node $classNode Узел класса
     */
    private function buildPropertyRelationship($relationship, $classNode)
    {
        // Свойство для хранения связи
        $builder = $this->factory->property($relationship->getName())->makeProtected();

        // Для toMany свойств связей моделей не имеющих хранилищей ставим значением по-умолчанию пустой массив
        $this->addPropertyRelationshipDefaultValue($builder, $relationship);

        $propertyNode = $builder->getNode();

        // Комментарий к свойству
        $this->addDocComment($propertyNode, 'Relate ' . $relationship->getName(), 1);

        // Добавляем свойство
        $classNode->addStmt($propertyNode);

        // Добавляем геттер
        $this->addRelationshipGetter($classNode, $relationship);

        // Добавляем сеттер
        $this->addPropertySetter($classNode, $relationship);

        // Для связи OneToMany добавляем метод для добавления элемента в набор
        if ($relationship->getType() == 'OneToMany') {
            $this->addRelationshipAppender($classNode, $relationship);
        }
    }


    /**
     * Добавляем геттер для связи c хранимой моделью
     *
     * @param Node $classNode Узел класса
     * @param Relationship $relationship Связь
     */
    private function addRelationshipGetter($classNode, $relationship)
    {
        // Геттер
        $getter_node = $this->factory->method('get' . ucfirst($relationship->getName()))->makePublic();

        if ($relationship->getType() === 'OneToMany' || $relationship->getType() === 'ManyToMany') {
            $getter_node->addStmts($this->parser->parse($this->getRelationshipToManyGetterCode($relationship)));
        } else {
            $getter_node->addStmts($this->parser->parse($this->getRelationshipToOneGetterCode($relationship)));
        }

        $getter_node = $getter_node->getNode();

        // Комментарий к геттеру
        $this->addDocComment($getter_node, 'Get related ' . $relationship->getName(), 1);

        // Добавляем геттер
        $classNode->addStmt($getter_node);
    }


    /**
     * Возвращает исходный код геттера для связи ManyToOne
     *
     * @param Relationship $relationship Свойство связи
     *
     * @return string
     */
    private function getRelationshipToManyGetterCode($relationship)
    {
        $this->needServiceContainer = true;

        return "<?php
                if (count(\$this->{$relationship->getName()}) === 0)
                {
                    if (property_exists(\$this, 'id') && !empty(\$this->id)) {
                        \$this->{$relationship->getName()} = \$this->container->get('ag.storage')->findRelated('{$relationship->getModel()->getFullName()}', '{$relationship->getTarget()->getFullName()}', \$this->getId(), '{$relationship->getName()}');
                    }
                }
                return \$this->{$relationship->getName()};
                ";
    }


    /**
     * Возвращает исходный код геттера для связи OneToOne
     *
     * @param Relationship $relationship Свойство связи
     *
     * @return string
     */
    private function getRelationshipToOneGetterCode($relationship)
    {
        if ($target = $relationship->getTarget()) {
            $this->needServiceContainer = true;

            return "<?php
                if (count(\$this->{$relationship->getName()}) === 0)
                {
                    if (property_exists(\$this, 'id') && !empty(\$this->id)) {
                        \$related = \$this->container->get('ag.storage')->findRelated('{$relationship->getModel()->getFullName()}', '{$target->getFullName()}', \$this->getId(), '{$relationship->getName()}');
                        if (count(\$related) > 0) {
                            \$this->{$relationship->getName()} = \$related[0];
                        }

                    }
                }
                return \$this->{$relationship->getName()};
                ";
        }

        return "<?php
                return \$this->{$relationship->getName()};
                ";
    }


    /**
     * Добавляем метод для добавления элемента в коллекцию
     *
     * @param Node $classNode Узел класса
     * @param Relationship $relationship Связь
     */
    private function addRelationshipAppender($classNode, $relationship)
    {
        $addNode = $this->factory->method('addItemTo' . ucfirst($relationship->getName()))->makePublic();
        $addNode->addParam($this->factory->param('item'));

        $code = '<?php
                $this->' . $relationship->getName() . '[] = $item;

                return $this;
                ';

        $addNode->addStmts($this->parser->parse($code));
        $classNode->addStmt($addNode);
    }


    /**
     * Подключаем логику к классу через атомы
     *
     * @param Model $model Модель
     * @param Node $classNode Узел класса
     */
    private function buildLogic($model, $classNode)
    {
        $tm = $this->container->get('ag.service.tagged_manager');
        foreach ($tm->findServices('ag.model_method') as $service) {

            // Если найден атом для текущей модели
            if (array_key_exists('model_name', $service['attributes']) && $service['attributes']['model_name'] == $model->getFullName()) {

                $this->needServiceContainer = true;

                // Если атом реализует какой-либо интерфейс - добавляем этот интерфейс в имплементацию класса
                if (array_key_exists('interface', $service['attributes'])) {
                    $classNode->implement($service['attributes']['interface']);
                }

                $atomMethodName = $this->getAtomMethodName($service['id'], $service['attributes']);

                $logicServiceClass = get_class($this->container->get($service['id']));

                // Используя рефлексию будем получать список параметров атома
                $methodReflection = new \ReflectionMethod($logicServiceClass, $atomMethodName);
                $methodParameters = $methodReflection->getParameters();

                // Генерируем на основе имени класса название метода по которому сервис будет доступен в модели
                $atomModelMethodName = lcfirst((new \ReflectionClass($logicServiceClass))->getShortName());

                // Создаем метод
                $method_node = $this->factory->method($atomModelMethodName)->makePublic();

                // Добавляем параметры к методу
                // Начинаем со второго параметра, так как первым параметром в атом идет инстанц модели
                for ($i = 1; $i < count($methodParameters); $i++) {
                    $param = $this->factory->param($methodParameters[$i]->getName());
                    if ($methodParameters[$i]->isDefaultValueAvailable()) {
                        $param->setDefault($methodParameters[$i]->getDefaultValue());
                    }
                    $method_node->addParam($param);
                }

                // Список параметров для кода вызова атома
                $atomCallParameters = array('$this');
                for ($i = 1; $i < count($methodParameters); $i++) {
                    $atomCallParameters[] = '$' . $methodParameters[$i]->getName();
                }

                // Содержимое метода
                $code = '<?php
                         return $this->container->get("' . $service['id'] . '")->' . $atomMethodName . '(' . implode(', ', $atomCallParameters) . ');';
                $method_node->addStmts($this->parser->parse($code));

                // Добавляем метод к классу
                $classNode->addStmt($method_node);
            }
        }
    }

    /**
     * Получаем имя метода атома на основе его идентификатора
     *
     * @param string $id ID атома
     * @param array $attributes Атрибуты атома
     *
     * @return string Имя метода атома
     */
    protected function getAtomMethodName($id, $attributes)
    {
        // Имя метода генерируется автоматически на основе имени класса атома, но бывают случаи, когда имя должно
        // быть конкретное (к примеру, для реализации какого-либо интерфейса)
        if (array_key_exists('atom_method_name', $attributes)) {
            return $attributes['atom_method_name'];
        }

        // Если разбить имя сервиса на слова (c расчетом что каждое разделено с помощью _),
        // то имя метода атома совпадет с первым словом из имени сервиса
        $serviceIdParts = explode('.', $id);
        $serviceName = array_pop($serviceIdParts);
        $serviceNameParts = explode('_', $serviceName);

        return array_shift($serviceNameParts);
    }


    /**
     * Генерируем метод __toString
     *
     * @param Model $model Модель
     * @param Node $classNode Узел класса
     */
    private function buildStringRepresentation($model, $classNode)
    {
        // __toString может подключаться через атомы
        $tm = $this->container->get('ag.service.tagged_manager');
        foreach ($tm->findServices('ag.model_method') as $service) {
            // Если найден атом для текущей модели
            if (array_key_exists('model_name', $service['attributes']) && $service['attributes']['model_name'] == $model->getFullName()) {

                if (array_key_exists('model_method_name', $service['attributes']) && $service['attributes']['model_method_name'] === '__toString') {
                    return;
                }
            }
        }
        $method_node = $this->factory->method('__toString')->makePublic();

        foreach ($model->getAllFields() as $field) {
            if ($field->getRepresentation()) {

                $code = sprintf('<?php return (string)$this->%s;', $field->getName());
                $method_node->addStmts($this->parser->parse($code));
                $classNode->addStmt($method_node);

                return;
            }
        }

        foreach ($model->getAllFields() as $field) {
            if ($field instanceof Field\Scalar\Id) {

                $code = sprintf('<?php return \'%s #\' . $this->id;', $model->getName());
                $method_node->addStmts($this->parser->parse($code));
                $classNode->addStmt($method_node);

                return;
            }
        }

        $code = sprintf('<?php return \'%s\';', $model->getName());
        $method_node->addStmts($this->parser->parse($code));
        $classNode->addStmt($method_node);
    }


    /**
     * Сохраняет исходный код модели в соответсующий файл
     *
     * @param string $path Путь к файлу исходного кода
     * @param string $sourceCode Исходный код модели
     */
    private function saveSource($path, $sourceCode)
    {
        $dirPath = dirname($path);

        // Создаем директорию если ее не существует
        if (!file_exists($dirPath)) {
            mkdir($dirPath, 0777, true);
        }

        // Сохраняем
        file_put_contents($path, $sourceCode);
    }


    /**
     * Генерирует док-комментарий из переданных строк комментария и добавляет к узлу
     *
     * @param Node $node Узел к которому добавляется комментарий
     * @param array|string $comment Комментарий в виде строки или набора строк
     * @param int $verticalOffsetLineCount Количество пустых строк перед комментарием
     */
    private function addDocComment($node, $comment, $verticalOffsetLineCount = 0)
    {
        // Если комментарий передан в виде одной строки - разбиваем на несколько строк
        if (is_string($comment)) {
            $lines = array_map('trim', explode("\n", $comment));
        } else {
            $lines = $comment;
        }

        $formattedComment = str_pad(PHP_EOL, $verticalOffsetLineCount);
        $formattedComment .= '/**' . PHP_EOL;
        foreach ($lines as $line) {
            $formattedComment .= ' * ' . $line . PHP_EOL;
        }
        $formattedComment .= ' */';

        $node->setAttribute('comments', array(new PhpParser\Comment\Doc($formattedComment)));
    }


    /**
     * Добавляет геттер к классу для поля
     *
     * @param string $field Поле
     * @param Node $classNode Узел класса
     *
     * @return Node Узел класса
     */
    private function addFieldGetter($field, $classNode)
    {
        if (strlen($field->getServiceName()) == 0) {
            $code = $this->getPropertyGetterCode($field);
        } else {
            $code = $this->getFieldFunctionGetterCode($field);
        }

        $getterNode = $this->factory->method('get' . ucfirst($field->getName()))->makePublic();

        $getterNode->addStmts($this->parser->parse($code));

        $getterNode = $getterNode->getNode();

        // Комментарий к геттеру
        $this->addDocComment($getterNode, 'Get ' . $field->getName(), 1);

        $classNode->addStmt($getterNode);

        return $classNode;
    }


    /**
     * Возвращает код геттера для свойства
     *
     * @param Property $property Свойство
     *
     * @return string
     */
    private function getPropertyGetterCode($property)
    {
        return '<?php return $this->' . $property->getName() . ';';
    }


    /**
     * Возвращает код геттера для калькулируемого поля
     *
     * @param string $field Поле
     *
     * @return string
     */
    private function getFieldFunctionGetterCode($field)
    {
        return '<?php return $this->container->get("' . $field->getServiceName() . '")->get($this);';
    }


    /**
     * Добавляет cеттер к классу для свойства
     *
     * @param Node $classNode Узел класса
     * @param Property $property Свойство
     */
    private function addPropertySetter($classNode, $property)
    {
        $setterNode = $this->factory->method('set' . ucfirst($property->getName()))->makePublic();

        // Устанавливаемый параметр для сеттера
        $setterParam = $this->factory->param($property->getName());
        $setterNode->addParam($setterParam);

        // Для скалярных свойств можно использовать приведение типов
        if ($property instanceof Integer) {
            $assignToCode = '(int)$' . $property->getName();
        } elseif ($property instanceof Boolean) {
            $assignToCode = '(empty($' . $property->getName() . ') ? 0 : 1)';
        } else {
            $assignToCode = '$' . $property->getName();
        }

        $code = '<?php $this->' . $property->getName() . ' = ' . $assignToCode . '; return $this;';

        $setterNode->addStmts($this->parser->parse($code));

        $setterNode = $setterNode->getNode();

        // Комментарий к геттеру
        $this->addDocComment($setterNode, 'Set ' . $property->getName(), 1);

        // Добавляем сеттер
        $classNode->addStmt($setterNode);
    }


    /**
     * Устанавливаем значение по-умолчанию для поля
     *
     * @param PhpParser\Builder\Property $builder Property builder
     * @param Field $field Поле
     *
     * @return void
     */
    private function addPropertyFieldDefaultValue($builder, $field)
    {
        $defaultValue = $field->getDefaultValue();
        if (!is_null($defaultValue)) {

            // Так как значение по-умолчаню хранится как строка, его надо привести к типу поля
            if ($field instanceof Integer) {
                $value = (int)$defaultValue;
            } elseif ($field instanceof Boolean) {
                $value = (int)$defaultValue;
            } else {
                $value = $defaultValue;
            }
            $builder->setDefault($value);
        }
    }


    /**
     * Устанавливаем значение по-умолчанию для свойства связи
     *
     * @param PhpParser\Builder\Property $builder Property builder
     * @param Relationship $relationship Свойство связи
     *
     * @return void
     */
    private function addPropertyRelationshipDefaultValue($builder, $relationship)
    {
        if (in_array($relationship->getType(), array('OneToMany', 'ManyToMany'))) {
            $builder->setDefault(array());
        }
    }
}