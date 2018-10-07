<?php namespace Blade\Database\Test\Unit\SqlBuilder;

use Blade\Database\Sql\SqlBuilder;

/**
 * @see \Blade\Database\Sql\SqlBuilder
 */
class AllTest extends \PHPUnit_Framework_TestCase
{
    use TestSqlTrait;

    /**
     * LABEL
     */
    public function testLabel()
    {
        $sql = $this->sql($label = "abc");
        $this->assertEquals("/*{$label}*/\nSELECT *\nFROM ".$this->table, $sql->toSql());

        $sql->setLabel($label = __METHOD__);
        $this->assertEquals("/*{$label}*/\nSELECT *\nFROM ".$this->table, $sql->toSql());
    }


    /**
     * FROM
     */
    public function testFrom()
    {
        $sql = $this->sql()
            ->from($this->table, 't');

        $this->assertEquals("SELECT *\nFROM {$this->table} AS t", $sql->toSql());
    }


    /**
     * FROM SELECT
     */
    public function testFromSelect()
    {
        $sql = $this->sql()
            ->select('id')
            ->from($this->sql()->from('some_table', 't'), 't1');

        $this->assertEquals("SELECT id\nFROM (SELECT *\nFROM some_table AS t) AS t1", $sql->toSql());
    }


    /**
     * ORDER BY
     */
    public function testOrder()
    {
        $sql = $this->sql();
        $sql->addOrder($a = 'col1');
        $sql->addOrder($b = 'col2 DESC');
        $this->assertEquals("SELECT *\nFROM {$this->table}\nORDER BY {$a}, {$b}", $sql->toSql());
    }


    /**
     * LIMIT ... OFFSET
     */
    public function testLimit()
    {
        $sql = $this->sql();
        $sql->limit(10);
        $this->assertEquals("SELECT *\nFROM {$this->table}\nLIMIT 10", $sql->toSql());
    }


    /**
     * ALL Select
     */
    public function testComplete()
    {
        $sql = $this->sql($label = 'label')
            ->addSelect('count(*)')->addSelect('col1')
            ->from($this->table, 't')
            ->addJoin($t2 = 'INNER JOIN table2 AS t2 USING (col)')
            ->andWhere($where1 = 'a=1')
            ->andWhere($where2 = 'b=2')
            ->groupBy($groupBy = 'col1, col2')
            ->having($having = 'sum(col1)>1')
            ->addOrder($order = 'col1')
            ->limit(10, 2);

        $this->assertEquals("/*{$label}*/".PHP_EOL.
            "SELECT count(*), col1".PHP_EOL.
            "FROM {$this->table} AS t".PHP_EOL.
            "{$t2}".PHP_EOL.
            "WHERE {$where1} AND {$where2}".PHP_EOL.
            "GROUP BY {$groupBy}".PHP_EOL.
            "HAVING {$having}".PHP_EOL.
            "ORDER BY {$order}".PHP_EOL.
            "LIMIT 10 OFFSET 2", $sql->toSql());
    }
}
