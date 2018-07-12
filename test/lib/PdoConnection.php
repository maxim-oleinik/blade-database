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
     * Выполнить SQL и для каждой строки выборки вызвать указанную callback-функцию
     * callback принимает строку ТОЛЬКО как МАССИВ
     *
     * @param string   $sql
     * @param array    $bindings
     * @param callable $callback
     */
    public function each($sql, $bindings = [], callable $callback)
    {
        if (!$statement = $this->query($sql)) {
            throw new \RuntimeException(var_export($this->errorInfo(), true) . PHP_EOL . $sql);
        }

        foreach ($statement as $row) {
            $callback($row);
        }
    }
}
