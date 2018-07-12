<?php namespace Blade\Database\Test;

use Blade\Database\DbConnectionInterface;

class PdoConnection extends \PDO implements DbConnectionInterface
{
    /**
     * Выполнить запрос
     *
     * @param string $sql
     * @param array $bindings
     * @return bool|int
     */
    public function execute($sql, $bindings = []): int
    {
        $result = $this->exec($sql);
        if (false === $result) {
            throw new \RuntimeException(var_export($this->errorInfo(), true) . PHP_EOL . $sql);
        }
        return (int) $result;
    }

    /**
     * Выполнить запрос и вернуть структуру доступную для foreach
     * или bool, если запрос не предполагает возврат значений
     *
     * @param string $sql
     * @param array  $bindings
     * @return array|bool|\PDOStatement
     */
    public function select($sql, $bindings = [])
    {
        $result = $this->query($sql);
        if (false === $result) {
            throw new \RuntimeException(var_export($this->errorInfo(), true) . PHP_EOL . $sql);
        }

        return $result;
    }
}
