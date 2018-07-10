<?php namespace Blade\Database\Test;

use Blade\Database\DbConnectionInterface;

class TestDbConnection implements DbConnectionInterface
{
    public $log = [];
    public $returnValues = [];
    private $queryCount = -1;

    public function query($sql, $bindings = [])
    {
        $this->log[] = (string)$sql;
        $this->queryCount++;
        if (isset($this->returnValues[$this->queryCount])) {
            return $this->returnValues[$this->queryCount];
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
}
