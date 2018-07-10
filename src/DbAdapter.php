<?php namespace Blade\Database;

use Blade\Database\Sql\SqlBuilder;

/**
 * @see \Test\BladeDatabase\DbAdapterTest
 */
class DbAdapter
{
    /**
     * @var \Blade\Database\DbConnectionInterface
     */
    private $connection;

    /**
     * DbAdapter constructor.
     *
     * @param \Blade\Database\DbConnectionInterface $connection
     */
    public function __construct(DbConnectionInterface $connection)
    {
        $this->connection = $connection;
    }


    /**
     * @return \Blade\Database\DbConnectionInterface
     */
    public function getConnection()
    {
        return $this->connection;
    }


    /**
     * Выполнить запрос не предполагающий возврат значений
     *
     * @param string $query
     * @param array $bindings
     * @return bool|int
     */
    public function execute($query, array $bindings = [])
    {
        return $this->getConnection()->execute($query, $bindings);
    }


    /**
     * Вернуть список ВСЕХ строк
     *
     * @param string $query
     * @param array  $bindings
     * @return array
     */
    public function selectList($query, array $bindings = []): array
    {
        if ($rows = $this->getConnection()->select((string)$query, $bindings)) {
            return $rows;
        }

        return [];
    }


    /**
     * Вернуть ОДНУ строку
     *
     * @param string $query
     * @param array  $bindings
     * @return array
     */
    public function selectRow($query, array $bindings = []): array
    {
        if ($rows = $this->getConnection()->select($query, $bindings)) {
            foreach ($rows as $row) {
                return (array) $row;
            }
        }

        return [];
    }

    /**
     * Вернуть КОЛОНКУ ввиде массива
     *
     * @param string $query
     * @param array  $bindings
     * @return array
     */
    public function selectColumn($query, array $bindings = []): array
    {
        $result = [];
        if ($rows = $this->getConnection()->select($query, $bindings)) {
            foreach ($rows as $row) {
                $row = (array) $row;
                $result[] = current($row);
            }
        }

        return $result;
    }


    /**
     * Вернуть значение ОДНОЙ ЯЧЕЙКИ
     *
     * @param string $query
     * @param array  $bindings
     * @return string|null
     */
    public function selectValue($query, array $bindings = [])
    {
        if ($row = $this->selectRow($query, $bindings)) {
            return current($row);
        }
    }


    /**
     * Key-Value
     *
     * @param string $query
     * @param array  $bindings
     * @return array
     */
    public function selectKeyValue($query, array $bindings = []): array
    {
        $result = [];
        foreach ($this->selectList($query, $bindings) as $row) {
            $row = (array) $row;
            if (count($row) < 2) {
                throw new \InvalidArgumentException(__METHOD__ . ": Expected min 2 columns, got ", count($row));
            }
            $row = array_values($row);
            $result[$row[0]] = $row[1];
        }

        return $result;
    }


    /**
     * Выполнить код в транзакции
     *
     * @param  callable $func
     * @return mixed
     * @throws \Exception
     */
    public function transaction(Callable $func)
    {
        $con = $this->getConnection();
        $con->beginTransaction();
        try {
            $result = $func();
            $con->commit();
            return $result;

        } catch (\Exception $e) {
            $con->rollBack();
            throw $e;
        }
    }


    /**
     * Разбить выборку на части
     *
     * @param int        $pageSize
     * @param SqlBuilder $sql
     * @param callable   $handler
     */
    public function chunk($pageSize, SqlBuilder $sql, callable $handler)
    {
        $rowsCount = $this->selectValue($sql->copy()->count());

        if ($rowsCount) {
            $itemsLeft = $rowsCount;
            $page   = 1;
            $offset = 0;
            do {
                $pageSql = $sql->copy()->limit($pageSize, $offset);
                $page++;
                $offset = ($page - 1) * $pageSize;
                $itemsLeft -= $pageSize;
                $handler($this->selectList($pageSql));
            } while ($itemsLeft > 0);
        }
    }
}
