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


    // Transaction
    // ------------------------------------------------------------------------

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
     * Run callback within transaction
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


    // Execute
    // ------------------------------------------------------------------------

    /**
     * Выполнить запрос не предполагающий возврат значений
     *
     * @param string $query
     * @param array $bindings
     * @return bool|int
     */
    public function execute($query, $bindings = [])
    {
        return $this->getConnection()->execute($query, $bindings);
    }


    // Select
    // ------------------------------------------------------------------------

    /**
     * Вернуть список ВСЕХ строк
     *
     * @param string $query
     * @param array  $bindings
     * @return array
     */
    public function selectList($query, $bindings = []): array
    {
        $result = [];

        $this->getConnection()->each((string)$query, $bindings, function ($row) use (&$result) {
            $this->_checkCallbackArguments($row);
            $result[] = $row;
        });

        return $result;
    }

    /**
     * Вернуть КОЛОНКУ ввиде массива
     *
     * @param string $query
     * @param array  $bindings
     * @return array
     */
    public function selectColumn($query, $bindings = []): array
    {
        $result = [];

        $this->getConnection()->each((string)$query, $bindings, function ($row) use (&$result) {
            $this->_checkCallbackArguments($row);
            $result[] = current($row);
        });

        return $result;
    }

    /**
     * Key-Value
     *
     * @param string $query
     * @param array  $bindings
     * @return array
     */
    public function selectKeyValue($query, $bindings = []): array
    {
        $result = [];

        $this->getConnection()->each((string)$query, $bindings, function ($row) use (&$result) {
            $this->_checkCallbackArguments($row);
            if (count($row) != 2) {
                throw new \RuntimeException(__METHOD__ . ": Expected 2 columns, got " . var_export($row, true));
            }
            $result[current($row)] = next($row);
        });

        return $result;
    }

    /**
     * Вернуть ОДНУ строку
     *
     * @param string $query
     * @param array  $bindings
     * @return array
     */
    public function selectRow($query, $bindings = []): array
    {
        if ($rows = $this->selectList($query, $bindings)) {
            foreach ($rows as $row) {
                return $row;
            }
        }

        return [];
    }

    /**
     * Вернуть значение ОДНОЙ ЯЧЕЙКИ
     *
     * @param string $query
     * @param array  $bindings
     * @return string|false - если ничего не найдено
     */
    public function selectValue($query, $bindings = [])
    {
        if ($row = $this->selectRow($query, $bindings)) {
            return current($row);
        }
        return false;
    }

    /**
     * Выкинуть исключение, если входящее значение не массив
     *
     * @param mixed $row
     */
    private function _checkCallbackArguments($row)
    {
        if (!is_array($row)) {
            throw new \InvalidArgumentException(__METHOD__ . ": Expected " . get_class($this->getConnection()) . "->each() runs callback with row as ARRAY");
        }
    }


    // Misc
    // ------------------------------------------------------------------------

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
