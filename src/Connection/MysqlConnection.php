<?php namespace Blade\Database\Connection;

use Blade\Database\DbConnectionInterface;

class MysqlConnection implements DbConnectionInterface
{
    private $host;
    private $user;
    private $pass;
    private $dbName;
    private $port;
    private $socket;

    /**
     * @var \mysqli
     */
    private $connection;

    /**
     * Конструктор
     *
     * @param string $host
     * @param string $user
     * @param string $pass
     * @param string $dbName
     * @param int    $port
     * @param string $socket
     */
    public function __construct($host = null, $user = null, $pass = null, $dbName = null, $port = null, $socket = null)
    {
        $this->host = $host;
        $this->user = $user;
        $this->pass = $pass;
        $this->dbName = $dbName;
        $this->port = $port;
        $this->socket = $socket;
    }


    /**
     * @return \mysqli - Соедениение с базой
     */
    public function getConnection()
    {
        if (!$this->connection) {
            $this->connection = new \mysqli($this->host, $this->user, $this->pass, $this->dbName, $this->port, $this->socket);
            if ($this->connection->connect_error) {
                throw new \RuntimeException(__METHOD__.": Connection failed: [{$this->connection->connect_errno}] {$this->connection->connect_error}");
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
        $this->_query($sql, $bindings, false);
        $c = (int) $this->connection->affected_rows;
        return $c;
    }


    /**
     * {@inheritdoc}
     */
    public function each($sql, $bindings = [], callable $callback)
    {
        $result = $this->_query($sql, $bindings);

        if ($result->num_rows) {
            foreach ($result as $row) {
                $callback($row);
            }
        }

        $result->close();
    }


    /**
     * {@inheritdoc}
     */
    public function escape($value): string
    {
        return (string)$this->getConnection()->real_escape_string($value);
    }


    /**
     * Выполнить запрос и вернуть resource-результат
     *
     * @param string $sql
     * @param array  $bindings
     * @param bool   $useResult
     * @return \mysqli_result
     */
    private function _query($sql, $bindings = [], $useResult = false)
    {
        $c = $this->getConnection();
        if ($bindings) {
            throw new \RuntimeException(__METHOD__.": NOT Implemented");
        } else {
            $result = $c->query($sql, $useResult ? MYSQLI_USE_RESULT :  MYSQLI_STORE_RESULT);
        }

        if (!$result) {
            throw new \RuntimeException('Query ERROR: ' . $c->error . PHP_EOL . 'Query: ' . $sql);
        }

        return $result;
    }
}
