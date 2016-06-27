<?php

namespace AppGear\PlatformBundle\Service\Entity\Model;

use AppGear\PlatformBundle\Entity\Model\Property\Field;
use AppGear\PlatformBundle\Entity\Model\Property\Field\Scalar\Id;
use AppGear\PlatformBundle\Entity\Model\Property\Relationship;
use AppGear\PlatformBundle\Entity\Model\Property\Relationship\Mapped;
use AppGear\PlatformBundle\Service\TaggedManager;
use AppGear\PlatformBundle\Storage\Mysql\Utils;
use Doctrine\DBAL\Driver\Connection;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Класс атома реализующего генерацию схемы таблицы MySQL для модели
 */
class GenerateMysqlSchema
{
    /**
     * Container
     *
     * @var ContainerInterface
     */
    private $container;

    /**
     * Connection to the mysql database
     *
     * @var Connection
     */
    protected $db;

    /**
     * Tagged service manager
     *
     * @var TaggedManager
     */
    private $taggedManager;

    /**
     * Constructor
     *
     * @param ContainerInterface $container Container
     * @param Connection $db Connection to the mysql database
     * @param TaggedManager $taggedManager Tagged service manager
     */
    public function __construct(ContainerInterface $container, Connection $db, TaggedManager $taggedManager)
    {
        $this->container = $container;
        $this->db = $db;
        $this->taggedManager = $taggedManager;
    }

    /**
     * Генерирует схему таблицы MySQL для модели
     *
     * @param Model $model Модель
     *
     * @throws \RuntimeException
     *
     * @return string
     */
    public function generate($model)
    {
        // Doctrine Schema-Manager
        $sm = $this->db->getSchemaManager();

        // Текущая схема бд
        $fromSchema = $sm->createSchema();

        // Итоговая схема бд
        $toSchema = clone $fromSchema;

        // Название таблицы для модели
        $tableName = Utils::buildModelTableName($model);

        // Если таблица уже существует
        if ($fromSchema->hasTable($tableName)) {
            $table = $toSchema->getTable($tableName);
        } // Если таблицы не существует
        else {
            $table = $toSchema->createTable($tableName);
        }

        // Список полей таблицы полученных из модели
        $fieldsFromModel = array();

        // Список известных внешних ключей
        $knownForeignKeys = [
            $tableName => []
        ];
        $tableNameMatch  = [
            $tableName => $table
        ];

        // Поле идентификатора (оно должно идти первым в таблице)
        foreach ($model->getAllProperties() as $property) {

            if ($property instanceof Id) {

                $fieldExtension = $this->getFieldExtension($property);

                if ($table->hasColumn($property->getName())) {
                    $column = $table->getColumn($property->getName());
                } else {
                    $column = $table->addColumn($property->getName(), $fieldExtension->getColumnType());
                }

                $column->setOptions($fieldExtension->getColumnOptions());

                if (!$table->hasPrimaryKey()) {
                    $table->setPrimaryKey(array($property->getName()));
                }

                $fieldsFromModel[] = $property->getName();
            }
        }

        // Ключи для родительских таблиц
        if ($parentModel = $model->getParent()) {

            $parentTableName = Utils::buildModelTableName($parentModel);

            $inheritanceKeyExists = false;
            foreach ($table->getForeignKeys() as $fKey) {
                $localTableMatch = $fKey->getLocalTable()->getName() === $tableName;
                $localColumnNamesCountMatch = count($fKey->getLocalColumns()) === 1;
                $localColumnNameMatch = $fKey->getLocalColumns()[0] === 'id';
                $foreignTableMatch = $fKey->getForeignTableName() === $parentTableName;
                $foreignColumnNamesCountMatch = count($fKey->getForeignColumns()) === 1;
                $foreignColumnNameMatch = $fKey->getForeignColumns()[0] === 'id';

                if ($localTableMatch &&
                    $localColumnNamesCountMatch &&
                    $localColumnNameMatch &&
                    $foreignTableMatch &&
                    $foreignColumnNamesCountMatch &&
                    $foreignColumnNameMatch
                ) {
                    $knownForeignKeys[$tableName][] = $fKey->getName();
                    $inheritanceKeyExists = true;
                    break;
                }
            }

            if (!$inheritanceKeyExists) {
                $constraintName = 'fk_' . strtolower($tableName) . '_parent';
                $table->addForeignKeyConstraint($parentTableName, ['id'], ['id'], [], $constraintName);
                $knownForeignKeys[$tableName][] = $constraintName;
            }
        }

        // Генерируем список полей таблицы на основе связей модели
        foreach ($model->getProperties() as $property) {

            if ($property instanceof Relationship) {
                $isManyToOne = $property->getType() === 'ManyToOne';
                $isOneToOne = $property->getType() === 'OneToOne';
                $isMapped = $property instanceof Mapped;

                if ($isManyToOne || ($isOneToOne && $isMapped)) {

                    $columnName = $property->getName() . '_id';

                    if ($table->hasColumn($columnName)) {
                        $column = $table->getColumn($columnName);
                    } else {
                        $column = $table->addColumn($columnName, 'integer');
                    }

                    $column->setUnsigned(true);
                    $column->setNotNull(false);

                    $fieldsFromModel[] = $columnName;
                }
            }
        }


        // Генерируем связи таблиц на основе связей модели
        foreach ($model->getProperties() as $property) {

            if ($property instanceof Relationship) {

                $isManyToMany = $property->getType() === 'ManyToMany';
                $isManyToOne = $property->getType() === 'ManyToOne';
                $isOneToMany = $property->getType() === 'OneToMany';
                $isOneToOne = $property->getType() === 'OneToOne';
                $isMapped = $property instanceof Relationship\Mapped;

                if ($target = $property->getTarget()) {

                    $targetTableName = Utils::buildModelTableName($target);

                    $opposite = $property->getOpposite();
                    if ($opposite !== null) {
                        $constraintName = 'fk_' . strtolower($tableName . '_' . $property->getOpposite()->getName());
                    } else {
                        $constraintName = $this->_generateIdentifierName($targetTableName, [$property->getName() . '_id']);
                    }

                    if ($isManyToOne || ($isOneToOne && $isMapped)) {

                        $foreignKeyExists = false;
                        foreach ($table->getForeignKeys() as $fKey) {

                            $keyNameMatch = ($constraintName === null || $constraintName === $fKey->getName());
                            $localTableMatch = $fKey->getLocalTable()->getName() === $tableName;
                            $localColumnNamesCountMatch = count($fKey->getLocalColumns()) === 1;
                            $localColumnNameMatch = $fKey->getLocalColumns()[0] === $property->getName() . '_id';
                            $foreignTableMatch = $fKey->getForeignTableName() === $targetTableName;
                            $foreignColumnNamesCountMatch = count($fKey->getForeignColumns()) === 1;
                            $foreignColumnNameMatch = $fKey->getForeignColumns()[0] === 'id';

                            if ($keyNameMatch &&
                                $localTableMatch &&
                                $localColumnNamesCountMatch &&
                                $localColumnNameMatch &&
                                $foreignTableMatch &&
                                $foreignColumnNamesCountMatch &&
                                $foreignColumnNameMatch
                            ) {
                                $foreignKeyExists = true;
                                $knownForeignKeys[$tableName][] = $fKey->getName();
                                break;
                            }
                        }

                        if (!$foreignKeyExists) {
                            $table->addForeignKeyConstraint($targetTableName, [$property->getName() . '_id'], ['id'], [], $constraintName);
                            $knownForeignKeys[$tableName][] = $constraintName;
                        }
                    }

                    if (($isManyToMany || $isOneToMany) && $isMapped) {

                        $joinTableName  = $tableName . ucfirst($property->getName());

                        if ($fromSchema->hasTable($joinTableName)) {
                            $joinTable = $toSchema->getTable($joinTableName);
                        } else {
                            $joinTable = $toSchema->createTable($joinTableName);
                        }

                        if (!$joinTable->hasColumn('from_id')) {
                            $column = $joinTable->addColumn('from_id', 'integer');
                            $column->setUnsigned(true);
                            $column->setNotNull(true);
                        }

                        if (!$joinTable->hasColumn('to_id')) {
                            $column = $joinTable->addColumn('to_id', 'integer');
                            $column->setUnsigned(true);
                            $column->setNotNull(true);
                        }

                        $knownForeignKeys[$joinTableName] = [];
                        $tableNameMatch[$joinTableName] = $joinTable;

                        $secondTableName = Utils::buildModelTableName($property->getTarget());
                        $firstKeyExist = false;
                        $secondKeyExist = false;
                        foreach ($joinTable->getForeignKeys() as $fKey) {
                            $joinTableMatch = $fKey->getLocalTable()->getName() === $joinTableName;
                            $joinColumnNamesCountMatch = count($fKey->getLocalColumns()) === 1;

                            if ($joinTableMatch && $joinColumnNamesCountMatch) {
                                if (!$firstKeyExist) {
                                    $firstKeyNameMatch = $fKey->getName() === $property->getName();
                                    $firstJoinColumnNameMatch = $fKey->getLocalColumns()[0] === 'from_id';
                                    $firstTableMatch = $fKey->getForeignTableName() === $tableName;
                                    $firstColumnNamesCountMatch = count($fKey->getForeignColumns()) === 1;
                                    $firstColumnNameMatch = $fKey->getForeignColumns()[0] === 'id';
                                    if ($firstKeyNameMatch && $firstJoinColumnNameMatch && $firstTableMatch && $firstColumnNamesCountMatch && $firstColumnNameMatch) {
                                        $firstKeyExist = true;
                                        $knownForeignKeys[$joinTableName][] = $fKey->getName();
                                    }
                                }

                                if (!$secondKeyExist) {
                                    $oppositeExist = $property->getOpposite() !== null;
                                    $secondKeyNameMatch = $oppositeExist && ($fKey->getName() === $property->getOpposite()->getName());
                                    $secondJoinColumnNameMatch = $fKey->getLocalColumns()[0] === 'to_id';
                                    $secondTableMatch = $fKey->getForeignTableName() === $secondTableName;
                                    $secondColumnNamesCountMatch = count($fKey->getForeignColumns()) === 1;
                                    $secondColumnNameMatch = $fKey->getForeignColumns()[0] === 'id';
                                    if (($oppositeExist || $secondKeyNameMatch) && $secondJoinColumnNameMatch && $secondTableMatch && $secondColumnNamesCountMatch && $secondColumnNameMatch) {
                                        $secondKeyExist = true;
                                        $knownForeignKeys[$joinTableName][] = $fKey->getName();
                                    }
                                }
                            }
                        }

                        if (!$firstKeyExist) {
                            $constraintName = 'fk_' . strtolower($tableName . '_' . $property->getName());
                            $joinTable->addForeignKeyConstraint($tableName, ['from_id'], ['id'], [], $constraintName);
                            $knownForeignKeys[$joinTableName][] = $constraintName;
                        }
                        if (!$secondKeyExist) {

                            if ($property->getOpposite() !== null) {
                                $constraintName = 'fk_' . strtolower($secondTableName . '_' . $property->getOpposite()->getName());
                            } else {
                                $constraintName = $this->_generateIdentifierName($joinTableName, ['to']);
                            }
                            $joinTable->addForeignKeyConstraint($secondTableName, ['to_id'], ['id'], [], $constraintName);
                            $knownForeignKeys[$joinTableName][] = $constraintName;
                        }
                    }
                }
            }
        }

        // Удаляем неизвестные внешние ключи
        foreach ($knownForeignKeys as $knownForeignKeyTableName=>$knownForeignKeysNames) {
            $knownForeignKeyTable = $tableNameMatch[$knownForeignKeyTableName];
            foreach ($knownForeignKeyTable->getForeignKeys() as $foreignKey) {
                if (!in_array($foreignKey->getName(), $knownForeignKeysNames)) {
                    $knownForeignKeyTable->removeForeignKey($foreignKey->getName());
                }
            }
        }


        // Генерируем список полей таблицы на основе полей модели
        foreach ($model->getProperties() as $property) {

            if ($property instanceof Field) {
                if (!($property instanceof Id)) {

                    $fieldExtension = $this->getFieldExtension($property);

                    if ($table->hasColumn($property->getName())) {
                        $column = $table->getColumn($property->getName());
                    } else {
                        $column = $table->addColumn($property->getName(), $fieldExtension->getColumnType());
                    }

                    $column->setNotNull(false);
                    $column->setOptions($fieldExtension->getColumnOptions());

                    $fieldsFromModel[] = $property->getName();
                }
            }
        }

        // Если в таблице нет колонок и таблица новая - то ничего не делаем
        $isEmptyTable = count($table->getColumns()) === 0;
        $isNewTable = !$fromSchema->hasTable($tableName);
        if ($isEmptyTable && $isNewTable) {
            return [];
        }

        // Удаляем из таблицы поля, которые не были получены из модели
        foreach ($table->getColumns() as $column) {
            if (!in_array($column->getName(), $fieldsFromModel)) {
                $table->dropColumn($column->getName());
            }
        }

        // Поле дискриминатора
        if (($model->getParent() === null || $model->getParent()->getId() == 1) && !$table->hasColumn('_discriminator')) {
            $table->addColumn('_discriminator', 'string', ['default' => $model->getFullName()]);
        }

        $sql = $fromSchema->getMigrateToSql($toSchema, $this->db->getDatabasePlatform());

        return $sql;
    }

    /**
     * @see AbstractAsset::_generateIdentifierName
     */
    private function _generateIdentifierName($tableName, $localColumnNames)
    {
        $columnNames = array_merge((array) $tableName, $localColumnNames);
        $hash = implode("", array_map(function ($column) {
            return dechex(crc32($column));
        }, $columnNames));

        return substr('fk_' . $hash, 0, 63);
    }

    /**
     * Get field extension for mysql schema generation
     *
     * @param Field $field Field
     *
     * @return array|null
     */
    protected function getFieldExtension($field)
    {
        $name = (new \ReflectionClass(get_class($field)))->getShortName();

        $extensions = $this->taggedManager->findServices('ag.field.storage.mysql.type', ['alias' => strtolower($name)]);

        if (count($extensions) === 0) {
            throw new \RuntimeException(sprintf('Can\'t found field extension for the "%s"', $name));
        }

        $extension = array_shift($extensions);

        return $this->container->get($extension['id']);
    }
}