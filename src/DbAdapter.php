<?php namespace Blade\Database;

use Blade\Database\Sql\SqlBuilder;

/**
 * @see \Test\Blade\Database\DbAdapter\SelectTest
 * @see \Test\Blade\Database\DbAdapter\TransactionTest
 * @see \Test\Blade\Database\DbAdapter\ChunkTest
 */
class DbAdapter
{
    /**
     * @var \Blade\Database\DbConnectionInterface
     */
    private $connection;

    /**
     * @var int - Счетчик вложенных транзакций
     */
    private $transactionCounter = 0;

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
     * Start a new database transaction.
     */
    public function beginTransaction()
    {
        if (!$this->transactionCounter) {
            $this->getConnection()->beginTransaction();
        } else {
            $this->execute('SAVEPOINT ' . $this->_getSavePointName());
        }
        $this->transactionCounter++;
    }

    /**
     * Commit the active database transaction.
     */
    public function commit()
    {
        $this->transactionCounter--;
        if ($this->transactionCounter < 0) {
            throw new \RuntimeException(__METHOD__. ": No Active transaction, counter: " . $this->transactionCounter);

        } elseif (!$this->transactionCounter) {
            $this->getConnection()->commit();

        } else {
            $this->execute('RELEASE SAVEPOINT ' . $this->_getSavePointName());
        }
    }

    /**
     * Rollback the active database transaction.
     *
     * @return void
     */
    public function rollBack()
    {
        $this->transactionCounter--;
        if ($this->transactionCounter < 0) {
            throw new \RuntimeException(__METHOD__. ": No Active transaction, counter: " . $this->transactionCounter);

        } elseif (!$this->transactionCounter) {
            $this->getConnection()->rollBack();

        } else {
            $this->execute('ROLLBACK TO SAVEPOINT ' . $this->_getSavePointName());
        }
    }

    /**
     * @return string
     */
    private function _getSavePointName()
    {
        return 'sp' . $this->transactionCounter;
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
        $result = [];
        if ($rows = $this->getConnection()->select((string)$query, $bindings)) {
            if (!is_array($rows) && !$rows instanceof \Traversable) {
                throw new \RuntimeException(__METHOD__.": Expected ".get_class($this->getConnection())."->select({$query}) will return ARRAY or Traversable");
            }
            foreach ($rows as $row) {
                $result[] = $row;
            }
        }

        return $result;
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
        $this->beginTransaction();
        try {
            $result = $func();
            $this->commit();
            return $result;

        } catch (\Exception $e) {
            $this->rollBack();
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
