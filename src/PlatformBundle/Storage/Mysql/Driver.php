<?php

namespace AppGear\PlatformBundle\Storage\Mysql;

use AppGear\PlatformBundle\Cache\Manager;
use Cosmologist\Gears\ArrayType;
use Doctrine\DBAL\Connection;

class Driver
{
    /**
     * Doctrine DBAL connection
     *
     * @var Connection
     */
    protected $connection;

    /**
     * Cache manager
     *
     * @var Manager
     */
    protected $cache;

    /**
     * Foreign keys cache
     *
     * @var array
     */
    protected $foreignKeys;

    /**
     * Tables columns
     *
     * @var array
     */
    protected $columns = [];

    /**
     * Constructor
     *
     * @param Connection $connection Doctrine DBAL connection
     * @param Manager $cache Cache store
     */
    public function __construct(Connection $connection, Manager $cache)
    {
        $this->connection = $connection;
        $this->cache      = $cache;
    }


    /**
     * Get the table columns
     *
     * @param $table
     *
     * @return array
     */
    protected function getTableColumns($table)
    {
        if (!array_key_exists($table, $this->columns)) {
            $this->columns[$table] = array_column($this->connection->fetchAll('SHOW COLUMNS FROM `' . $table . '`'), 'Field');
        }

        return $this->columns[$table];
    }


    /**
     * Return list of the foreign keys for every table in the database
     *
     * @param string|null $fromTable From table name
     * @param string|null $fromColumn From column name
     * @param string|null $toTable To table
     * @param string|null $toColumn To column
     * @param string|null $constraintName Constraint name
     *
     * @return array
     */
    protected function getTablesForeignKeys($fromTable=null, $fromColumn=null, $toTable=null, $toColumn=null, $constraintName=null)
    {
        if ($this->foreignKeys === null) {
            $sql = 'SELECT
                        CONSTRAINT_NAME,
                        TABLE_NAME,
                        COLUMN_NAME,
                        REFERENCED_TABLE_NAME,
                        REFERENCED_COLUMN_NAME
                    FROM
                        information_schema.KEY_COLUMN_USAGE
                    WHERE TABLE_SCHEMA = ?
                        AND REFERENCED_TABLE_NAME IS NOT NULL';

            $this->foreignKeys = $this->connection->fetchAll($sql, [$this->connection->getDatabase()]);
        }

        $keys = [];

        foreach ($this->foreignKeys as $key) {
            if (!($fromTable === null || $fromTable === $key['TABLE_NAME'])) {
                continue;
            } elseif (!($fromColumn === null || $fromColumn === $key['COLUMN_NAME'])) {
                continue;
            } elseif (!($toTable === null || $toTable === $key['REFERENCED_TABLE_NAME'])) {
                continue;
            } elseif (!($toColumn === null || $toColumn === $key['REFERENCED_COLUMN_NAME'])) {
                continue;
            } elseif (!($constraintName === null || $constraintName === $key['CONSTRAINT_NAME'])) {
                continue;
            }

            $keys[] = $key;
        }

        return $keys;
    }


    /**
     * Get table inheritance information
     *
     * Inheritance is defined through the foreign keys between the tables
     *
     * @return array Pairs of table=>parent_table
     */
    protected function getTablesInheritanceMap()
    {
        $result = [];
        foreach ($this->getTablesForeignKeys(null, 'id', null, 'id') as $key) {
            $result[$key['TABLE_NAME']] = $key['REFERENCED_TABLE_NAME'];
        }

        return $result;
    }


    /**
     * Get related table
     *
     * @param string $table The table name
     * @param string $relationshipColumn The relationship column name
     *
     * @throws \RuntimeException When related table not found
     *
     * @return string
     */
    protected function getRelatedTable($table, $relationshipColumn)
    {
        foreach ($this->getTablesForeignKeys($table, $relationshipColumn) as $key) {
            return $key['REFERENCED_TABLE_NAME'];
        }

        throw new \RuntimeException("Column $relationshipColumn in the table $table does not exists");
    }


    /**
     * Find join table for passed table and key and return information about join table
     *
     * @param string $currentTable Current table name
     * @param string $currentColumn Current column
     *
     * @return array|null
     */
    protected function getJoinTableInformation($currentTable, $currentColumn)
    {
        foreach ($this->getTablesForeignKeys($currentTable) as $foreignKey) {
            $tableColumns = $this->getTableColumns($foreignKey['REFERENCED_TABLE_NAME']);
            $maybeJoinTable = count($tableColumns) === 2 && in_array($currentColumn, $tableColumns);

            if (!$maybeJoinTable) {
                continue;
            }

            $anotherColumn = $tableColumns[0] === $currentColumn ? $tableColumns[1] : $tableColumns[0];

            if ($anotherColumn === 'id') {
                continue;
            }

            return [
                'joinTable' => $foreignKey['REFERENCED_TABLE_NAME'],
                'currentColumn' => $currentColumn,
                'anotherColumn' => $anotherColumn
            ];
        }
    }


    /**
     * Get list of parents tables with the current table
     *
     * @param string $table        Table
     * @param bool   $withChildren Add children tables to the list
     * @param bool   $reverse      Reverse table order (default order is from parent to children)
     *
     * @return array List of parents tables
     */
    protected function getTableBranch($table, $withChildren = false, $reverse = false)
    {
        $branch = [];

        $tablesMap = $this->getTablesInheritanceMap();

        $current = $table;
        while (array_key_exists($current, $tablesMap)) {
            $current = $tablesMap[$current];
            array_unshift($branch, $current);
        }

        $branch[] = $table;

        if ($withChildren) {
            $current = $table;
            while ($current = array_search($current, $tablesMap)) {
                $branch[] = $current;
            }
        }

        if ($reverse) {
            $branch = array_reverse($branch);
        }

        return $branch;
    }


    /**
     * Build query with QueryBuilder for the inheritance tables
     *
     * @param string[] $tables The inheritance tables
     * @param array $conditions The query conditions
     * @param array $orders The query orders
     * @param int|null $limit Limit result set
     * @param int|null $offset Offset in the result set
     *
     * @return \Doctrine\DBAL\Query\QueryBuilder
     */
    protected function buildQuery($tables, array $conditions = [], array $orders = [], $offset = null, $limit = null)
    {
        $queryBuilder = $this->connection->createQueryBuilder();
        $queryBuilder->select('*');
        foreach ($tables as $i => $table) {
            if ($i === 0) {
                $queryBuilder->from($table, $table);
            } else {
                $queryBuilder->join($tables[0], $table, $table, $table . '.id = ' . $tables[0] . '.id');
            }
        }
        foreach ($conditions as $field => $value) {
            if ($field === 'id') {
                $field = $tables[0] . '.id';
            }
            if (is_array($value)) {
                $operator = ' IN ';
                $placeholder = '(?)';
            } else {
                if ($value !== null) {
                    $operator = ' = ';
                } else {
                    $operator = ' IS ';
                }
                $placeholder = '?';
            }

            $queryBuilder->andWhere($field . $operator . $placeholder);
        }
        foreach ($orders as $orderField=>$orderDirection) {
            if ($orderField === 'id') {
                $orderField = $tables[0] . '.id';
            }
            $queryBuilder->addOrderBy($orderField, $orderDirection);
        }

        if ($limit !== null) {
            $queryBuilder->setMaxResults($limit);
        }
        if ($offset !== null) {
            $queryBuilder->setFirstResult($offset);
        }

        return $queryBuilder;
    }


    /**
     * Get type for the parameter
     *
     * @param mixed $value Value
     *
     * @return int
     */
    protected function getParameterType($value)
    {
        if (is_int($value)) {
            return \PDO::PARAM_INT;
        }
        if (is_string($value)) {
            return \PDO::PARAM_INT;
        }
        if (is_array($value)) {
            if (count($value) > 0) {
                if (is_int($value[0])) {
                    return Connection::PARAM_INT_ARRAY;
                }
            }

            return Connection::PARAM_STR_ARRAY;
        }
        if ($value === null) {
            return \PDO::PARAM_NULL;
        }
        if (is_bool($value)) {
            return \PDO::PARAM_BOOL;
        }

        return \PDO::PARAM_STR;
    }


    /**
     * Build the parameters types
     *
     * @param mixed[] $parameters Parameters
     *
     * @return int[]
     */
    protected function buildParametersTypes($parameters)
    {
        return array_map([$this, 'getParameterType'], $parameters);
    }


    /**
     * Finds the table row by conditions.
     *
     * @param string $table The table name
     * @param bool $useInheritance Use table inheritance
     * @param array $conditions The find conditions
     * @param array $orders The query orders
     * @param int|null $limit Limit result set
     * @param int|null $offset Offset in the result set
     *
     * @return array
     */
    public function find($table, $useInheritance = true, array $conditions = [], array $orders = [], $limit = null, $offset = null)
    {
        // Build cache key
        $cacheKey = CacheHelper::buildCacheKey('find', $table, $useInheritance, $conditions, $orders, $limit, $offset);

        // Try to get result from cache
        if ($this->cache->contains($cacheKey)) {
            return $this->cache->fetch($cacheKey);
        }

        $result = $incompleteRows = [];

        $tables = $this->getTableBranch($table);
        $query = $this->buildQuery($tables, $conditions, $orders, $offset, $limit)->getSQL();
        $parameters = array_values($conditions);
        $parametersTypes = $this->buildParametersTypes($parameters);

        $rows = $this->connection->fetchAll($query, $parameters, $parametersTypes);

        foreach ($rows as $row) {
            if (!array_key_exists('_discriminator', $row)) {
                throw new \RuntimeException('Field _discriminator does not exists the "' . end($tables) . '" table');
            }

            if ($useInheritance) {
                $rowTable = $this->buildTableName($row['_discriminator']);

                if (!in_array($rowTable, $tables)) {
                    if (!array_key_exists($rowTable, $incompleteRows)) {
                        $incompleteRows[$rowTable] = [];
                    }
                    $incompleteRows[$rowTable][] = $row['id'];

                    continue;
                }
            }

            $result[] = $row;
        }

        foreach ($incompleteRows as $incompleteRowTable => $incompleteRowIds) {
            $tables = $this->getTableBranch($incompleteRowTable);
            $incompleteRowsConditions = $conditions;
            $incompleteRowsConditions['id'] = $incompleteRowIds;

            $query = $this->buildQuery($tables, $incompleteRowsConditions, $orders)->getSQL();
            $incompleteRowsParameters = array_values($incompleteRowsConditions);
            $incompleteRowsParametersTypes = $this->buildParametersTypes($incompleteRowsParameters);

            $rows = $this->connection->fetchAll($query, $incompleteRowsParameters, $incompleteRowsParametersTypes);
            foreach ($rows as $row) {
                $result[] = $row;
            }
        }

        // Save result to the cache
        $this->cache->save($cacheKey, $result);
        $this->cache->setTags($cacheKey, $tables);

        return $result;
    }


    /**
     * Finds the table row by its identifier.
     *
     * @param string $table The table name
     *
     * @return array
     */
    public function findById($table, $id)
    {
        $result = $this->find($table, true, ['id' => $id]);

        if (count($result) === 0) {
            throw new \RuntimeException($table . '#' . $id . ' does not exists');
        }

        return $result[0];
    }


    /**
     * Find related data
     *
     * @param string $fromTable From the table
     * @param string $toTable Related to the table
     * @param integer $fromId Related from ID
     * @param string $key Relationship key
     *
     * @throws \RuntimeException When relation is unexpected
     *
     * @return array
     */
    public function findRelated($fromTable, $toTable, $fromId, $key)
    {
        $fromColumn    = $key . '_id';
        $fromKeys      = $this->getTablesForeignKeys($fromTable, $fromColumn, $toTable);
        $fromKey       = array_pop($fromKeys);

        // ManyToOne and mapped OneToOne
        if ($fromKey !== null) {
            $row = $this->findById($fromTable, $fromId);
            $id  = $row[$fromColumn];

            if ($id === null) {
                return [];
            }
            return [$this->findById($toTable, $row[$fromColumn])];
        }

        $toKeys = $this->getTablesForeignKeys($toTable, null, $fromTable, null);
        $toKey  = array_pop($toKeys);

        // OneToMany and inversed OneToOne
        if ($toKey !== null) {
            return $this->find($toTable, true, [$toKey['COLUMN_NAME'] => $fromId]);
        }

        // ManyToMany
        $fromKeys = $this->getTablesForeignKeys(null, null, $fromTable, 'id', ('fk_' . strtolower($fromTable . '_' . $key)));
        $fromKey = array_pop($fromKeys);

        if ($fromKey !== null) {
            $toKeys = $this->getTablesForeignKeys($fromKey['TABLE_NAME'], null, $toTable, 'id');
            $toKey  = array_pop($toKeys);
        }

        if ($fromKey !== null && $toKey !== null) {

            $toIds = $this->connection->fetchAll("SELECT {$toKey['COLUMN_NAME']} FROM {$toKey['TABLE_NAME']} WHERE {$fromKey['COLUMN_NAME']} = ?", [$fromId]);
            $toIds = array_column($toIds, $toKey['COLUMN_NAME']);

            return $this->find($toTable, true, ['id' => $toIds]);
        }

        throw new \RuntimeException(sprintf('Unexpected relation between %s:%s#%s and %s', $fromTable, $key, $fromId, $toTable));
    }


    /**
     * Count of rows by conditions
     *
     * @param string $table The table name
     * @param array $conditions Conditions
     *
     * @return int
     */
    public function count($table, array $conditions = [])
    {
        // Build cache key
        $cacheKey = CacheHelper::buildCacheKey('count', $table, $conditions);

        // Try to get result from cache
        if ($this->cache->contains($cacheKey)) {
            return $this->cache->fetch($cacheKey);
        }

        $tables = $this->getTableBranch($table);
        $query = $this->buildQuery($tables, $conditions)->select('COUNT(*)')->getSQL();

        $parameters = array_values($conditions);
        $parametersTypes = $this->buildParametersTypes($parameters);

        $count = $this->connection->fetchColumn($query, $parameters, 0, $parametersTypes);

        // Save result to the cache
        $this->cache->save($cacheKey, $count);
        $this->cache->setTags($cacheKey, [$table]);

        return $count;
    }


    /**
     * Save the object to the database
     *
     * @param string $table The table name
     * @param array $data Data to save
     * @param string $discriminator Discriminator
     */
    public function save($table, $data, $discriminator)
    {
        $tables = $this->getTableBranch($table);

        // Should update rows or create new
        $update = array_key_exists('id', $data) && (int)$data['id'] > 0;

        foreach ($tables as $tableIndex=>$table) {

            $tableData = [];

            // Columns of the current table
            $tableColumns = $this->getTableColumns($table);

            // Matching table columns and the data items
            foreach ($data as $key=>$value) {
                if (in_array($key, $tableColumns)) {
                    $tableData[$this->connection->quoteIdentifier($key)] = $data[$key];
                } elseif (in_array($key . '_id', $tableColumns)) {
                    $tableData[$key . '_id'] = $data[$key];
                }
            }

            if ($update) {
                $this->connection->update($table, $tableData, ['id' => $data['id']]);
            } else {

                // Add discriminator value for first table
                if ($tableIndex === 0) {
                    $tableData['_discriminator'] = $discriminator;
                }

                $this->connection->insert($table, $tableData);

                // Get id for inherited tables
                if ($tableIndex === 0) {
                    $data['id'] = $this->connection->lastInsertId();
                }
            }

            // Clear cache
            $this->cache->deleteTags([$table]);
        }

        return $data['id'];
    }

    /**
     * Relate data
     *
     * @param string $fromTable From the table
     * @param string $toTable Related to the table
     * @param integer $fromId Related from ID
     * @param integer $toId Relate to ID
     * @param string $fromKey Relationship key
     *
     * @throws \RuntimeException When relation is unexpected
     *
     * @return array
     */
    public function relate($fromTable, $toTable, $fromId, $toId, $fromKey)
    {
        $fromTables = $this->getTableBranch($fromTable, false, true);
        $toTables   = $this->getTableBranch($toTable, false, true);

        if (is_array($toId)) {

            /* Logic for OneToMany relationship */

            foreach ($toTables as $table) {
                $keys = $this->getTablesForeignKeys($table, null, null, null, 'fk_' . strtolower($table . '_' . $fromKey));
                foreach ($keys as $key) {

                    $usedIds = array_column($this->connection->fetchAll("SELECT id FROM {$key['TABLE_NAME']} WHERE {$key['COLUMN_NAME']} = ?", [$fromId]), 'id');
                    foreach ($toId as $id) {
                        if (!in_array($id, $usedIds)) {
                            $this->connection->update($table, [$key['COLUMN_NAME'] => $fromId], ['id' => $toId]);
                        }
                        $usedIds = ArrayType::unsetValue($usedIds, $id);
                    }

                    foreach ($usedIds as $id) {
                        $this->connection->update($key['TABLE_NAME'], [$key['COLUMN_NAME'] => null], ['id' => $id]);
                    }

                    return true;
                }
            }

            /* Logic for ManyToMany relationship */

            $currentKey = null;
            foreach ($fromTables as $table) {
                $currentKeys = $this->getTablesForeignKeys(null, null, $table, 'id', ('fk_' . strtolower($table . '_' . $fromKey)));
                if ($currentKey = array_pop($currentKeys)) {
                    break;
                }
            }
            $anotherKey = null;
            if ($currentKey !== null) {
                foreach ($toTables as $table) {
                    $anotherKeys = $this->getTablesForeignKeys($currentKey['TABLE_NAME'], null, $table, 'id');
                    if ($anotherKey = array_pop($anotherKeys)) {
                        break;
                    }
                }
            }
            if ($currentKey !== null && $anotherKey !== null) {
                $currentRelations = $this->connection->fetchAll("SELECT {$anotherKey['COLUMN_NAME']} FROM {$currentKey['TABLE_NAME']} WHERE {$currentKey['COLUMN_NAME']} = ?", [$fromId]);
                $currentRelations = array_column($currentRelations, $anotherKey['COLUMN_NAME']);

                foreach ($toId as $id) {
                    if (!in_array($id, $currentRelations)) {
                        $this->connection->insert($currentKey['TABLE_NAME'], [$currentKey['COLUMN_NAME'] => $fromId, $anotherKey['COLUMN_NAME'] => $id]);
                    }
                }

                foreach ($currentRelations as $id) {
                    if (!in_array($id, $toId)) {
                        $this->connection->delete($currentKey['TABLE_NAME'], [$currentKey['COLUMN_NAME'] => $fromId, $anotherKey['COLUMN_NAME'] => $id]);
                    }
                }

                return true;
            }

        } else {

            /* Logic for ManyToOne and mapped OneToOne relationships */

            // Find table with same relationship field
            foreach ($fromTables as $table) {
                $columns = $this->getTableColumns($table);
                if (in_array($fromKey . '_id', $columns)) {
                    $this->connection->update($table, [$fromKey . '_id' => $toId], ['id' => $fromId]);

                    return true;
                }
            }

            /* Logic for inversed OneToOne relationships */

            foreach ($toTables as $table) {
                $keys = $this->getTablesForeignKeys($table);
                foreach ($keys as $key) {
                    if ($key['CONSTRAINT_NAME'] === 'fk_' . $table . '_' . $fromKey) {
                        $this->connection->update($table, [$key['COLUMN_NAME'] => $fromId], ['id' => $toId]);

                        return true;
                    }
                }
            }
        }

        return false;
    }


    /**
     * Remove record from the table
     *
     * @param string $table The table name
     * @param int $id ID
     */
    public function remove($table, $id)
    {
        $tables = $this->getTableBranch($table, true, true);
        foreach ($tables as $table) {
            $this->connection->delete($table, ['id' => $id]);

            // Clear cache
            $this->cache->deleteTags([$table]);
        }
    }


    /**
     * Build table name
     *
     * @param string $discriminator Discriminator
     *
     * @return string
     */
    protected function buildTableName($discriminator)
    {
        $tableName = str_replace('Bundle\\Entity\\', '\\', $discriminator);
        $tableName = str_replace('\\', '', $tableName);

        return $tableName;
    }
}