<?php namespace Blade\Database\Connection;

use Blade\Database\DbConnectionInterface;

class PdoConnection extends \PDO implements DbConnectionInterface
{
    /**
     * {@inheritdoc}
     */
    public function execute($sql, array $bindings = []): int
    {
        try {
            $result = $this->exec($sql);
        } catch (\Exception $e) {
            throw new \RuntimeException($e->getMessage() . PHP_EOL . $sql, null, $e);
        }
        if (false === $result) {
            throw new \RuntimeException(var_export($this->errorInfo(), true) . PHP_EOL . $sql);
        }
        return (int) $result;
    }

    /**
     * {@inheritdoc}
     */
    public function each($sql, array $bindings = []): \Generator
    {
        if (!$statement = $this->query($sql)) {
            throw new \RuntimeException(var_export($this->errorInfo(), true) . PHP_EOL . $sql);
        }

        foreach ($statement as $row) {
            yield $row;
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
