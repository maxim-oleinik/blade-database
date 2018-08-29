<?php namespace Blade\Database\Connection;

use Blade\Database\DbConnectionInterface;

class PostgresConnection implements DbConnectionInterface
{
    private $connectionString;
    private $connectType;
    private $connection;

    /**
     * Конструктор
     * см doc к pg_connect
     *
     * @param string $connectionString
     * @param int    $connectType
     */
    public function __construct($connectionString, $connectType = null)
    {
        $this->connectionString = $connectionString;
        $this->connectType = $connectType;
    }


    /**
     * @return resource - Соедениение с базой
     */
    public function getConnection()
    {
        if (!$this->connection) {
            if (!$this->connection = pg_connect($this->connectionString, $this->connectType)) {
                throw new \RuntimeException(__METHOD__.": Connection failed: " . $this->connectionString);
            }
        }
        return $this->connection;
    }


    /**
     * {@inheritdoc}
     */
    public function beginTransaction()
    {
        $this->execute('BEGIN');
    }

    /**
     * {@inheritdoc}
     */
    public function commit()
    {
        $this->execute('COMMIT');
    }

    /**
     * {@inheritdoc}
     */
    public function rollback()
    {
        $this->execute('ROLLBACK');
    }


    /**
     * {@inheritdoc}
     */
    public function execute($sql, $bindings = []): int
    {
        $result = $this->_query($sql, $bindings);
        $c = (int) pg_affected_rows($result);
        pg_free_result($result);
        return $c;
    }


    /**
     * {@inheritdoc}
     */
    public function each($sql, $bindings = [], callable $callback)
    {
        $result = $this->_query($sql, $bindings);

        while ($row = pg_fetch_assoc($result)) {
            $callback($row);
        }

        pg_free_result($result);
    }


    /**
     * {@inheritdoc}
     */
    public function escape($value): string
    {
        return pg_escape_string($this->getConnection(), $value);
    }


    /**
     * Выполнить запрос и вернуть resource-результат
     *
     * @param string $sql
     * @param array  $bindings
     * @return resource
     */
    private function _query($sql, $bindings = [])
    {
        $c = $this->getConnection();
        if ($bindings) {
            pg_send_query_params($c, $sql, $bindings);
        } else {
            pg_send_query($c, $sql);
        }

        $result = pg_get_result($c);
        if ($error = pg_result_error($result)) {
            throw new \RuntimeException('Query ERROR: ' . $error . PHP_EOL . 'Query: ' . $sql);
        }

        return $result;
    }
}
