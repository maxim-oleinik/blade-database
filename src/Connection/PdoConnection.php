<?php namespace Blade\Database\Connection;

use Blade\Database\DbConnectionInterface;

class PdoConnection extends \PDO implements DbConnectionInterface
{
    /**
     * {@inheritdoc}
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
     * {@inheritdoc}
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

    /**
     * {@inheritdoc}
     */
    public function escape($value): string
    {
        return substr($this->quote($value), 1, -1);
    }
}
