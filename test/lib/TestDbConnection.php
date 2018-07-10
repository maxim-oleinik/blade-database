<?php namespace Blade\Database\Test;

use Blade\Database\DbConnectionInterface;

class TestDbConnection implements DbConnectionInterface
{
    public $log = [];
    public $returnValue;

    public function query($sql, $bindings = [])
    {
        $this->log[] = $sql;
        return $this->returnValue;
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
