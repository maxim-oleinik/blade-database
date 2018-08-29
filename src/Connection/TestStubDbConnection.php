<?php namespace Blade\Database\Connection;

use Blade\Database\DbConnectionInterface;
use Blade\Database\Sql\SqlBuilder;

class TestStubDbConnection implements DbConnectionInterface
{
    public $log = [];
    public $returnValues = [];
    private $queryCount = -1;


    /**
     * Добавить набор строк, которые вернет N-ый запрос
     *
     * @param array $rows
     */
    public function addReturnResultSet(array $rows)
    {
        if ($rows) {
            $row = current($rows);
            if (!is_array($row) && !$row instanceof \StdClass) {
                throw new \InvalidArgumentException(__METHOD__.": Expected nested array: [[row1], [row2]]");
            }
        }
        $this->returnValues[] = $rows;
    }


    /**
     * {@inheritdoc}
     */
    public function execute($sql, $bindings = []):int
    {
        $this->log[] = (string)$sql;
        return 1;
    }


    /**
     * {@inheritdoc}
     */
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


    /**
     * {@inheritdoc}
     */
    public function beginTransaction()
    {
        $this->log[] = 'begin';
    }

    /**
     * {@inheritdoc}
     */
    public function commit()
    {
        $this->log[] = 'commit';
    }

    /**
     * {@inheritdoc}
     */
    public function rollBack()
    {
        $this->log[] = 'rollback';
    }


    /**
     * {@inheritdoc}
     */
    public function escape($value): string
    {
        return SqlBuilder::escape($value);
    }
}
