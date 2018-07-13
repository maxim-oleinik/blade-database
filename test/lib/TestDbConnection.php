<?php namespace Blade\Database\Test;

use Blade\Database\DbConnectionInterface;
use Blade\Database\Sql\SqlBuilder;

class TestDbConnection implements DbConnectionInterface
{
    public $log = [];
    public $returnValues = [];
    private $queryCount = -1;

    public function execute($sql, $bindings = []):int
    {
        $this->log[] = (string)$sql;
        return 1;
    }

    public function each($sql, $bindings = [], callable $callback)
    {
        $this->log[] = (string)$sql;
        $this->queryCount++;
        if (isset($this->returnValues[$this->queryCount])) {
            foreach ($this->returnValues[$this->queryCount] as $row) {
                $callback((array)$row);
            }
        }
    }

    public function beginTransaction()
    {
        $this->log[] = 'begin';
    }

    public function commit()
    {
        $this->log[] = 'commit';
    }

    public function rollBack()
    {
        $this->log[] = 'rollback';
    }

    public function escape($value): string
    {
        return SqlBuilder::escape($value);
    }
}
