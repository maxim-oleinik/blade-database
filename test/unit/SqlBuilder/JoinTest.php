<?php namespace Blade\Database\Test\Unit\SqlBuilder;

use Blade\Database\Sql\SqlBuilder;

/**
 * @see \Blade\Database\Sql\SqlBuilder
 */
class JoinTest extends \PHPUnit\Framework\TestCase
{
    use TestSqlTrait;


    /**
     * JOIN
     */
    public function testJoin()
    {
        $sql = $this->sql()
            ->select('t2.*')
            ->addJoin($t2 = 'INNER JOIN table2 AS t2 USING (col)')
            ->addJoin($t3 = 'LEFT JOIN table3 AS t3 USING (col)');
        $this->assertEquals("SELECT t2.*\nFROM {$this->table} AS t\n{$t2}\n{$t3}", $sqlStr = $sql->toSql());

        // Join once
        $sql->addJoin($t2, true);
        $this->assertEquals($sqlStr, $sql->toSql());
    }

    /**
     * JOIN - Merge SQL
     */
    public function testJoinMerge()
    {
        $sql2 = SqlBuilder::make('some label should be ignored')
            ->from('table2', 't2')
            ->select('*')
            ->andWhere('t2.col=123');

        $sql = $this->sql()->setFromAlias('t1')
            ->select('t1.*')
            ->innerJoin($sql2, 'ON t2.id=t1.id')
            ->leftJoin($sql2, 'ON t2.col=t1.col')
            ->rightJoin($sql2)
            ->andWhere('t1.col=55');

        $this->assertEquals("SELECT t1.*\nFROM {$this->table} AS t1\n".
            "INNER JOIN table2 AS t2 ON t2.id=t1.id\n" .
            "LEFT JOIN table2 AS t2 ON t2.col=t1.col\n" .
            "RIGHT JOIN table2 AS t2\n".
            "WHERE t2.col=123 AND t2.col=123 AND t2.col=123 AND t1.col=55", $sqlStr = $sql->toSql());

        // join once
        $sql->innerJoin($sql2, 'ON t2.id=t1.id', true)
            ->leftJoin($sql2, 'ON t2.col=t1.col', true)
            ->rightJoin($sql2, null, true);
        $this->assertEquals($sqlStr, $sql->toSql());
    }


    /**
     * JOIN без условий WHERE
     */
    public function testJoinWithoutWhere()
    {
        $sql2 = SqlBuilder::make('some label should be ignored')
            ->from('table2', 't2')
            ->select('*')
            ->andWhere('t2.col=123');

        $sql = $this->sql()->setFromAlias('t1')
            ->select('*')
            ->innerJoin(SqlBuilder::make()->from('table2', 't2'));

        $this->assertEquals("SELECT *\nFROM {$this->table} AS t1\nINNER JOIN table2 AS t2", $sql->toSql());
    }


    /**
     * JOIN - Exception if no SELECT
     */
    public function testJoinExceptionIfNoSelect()
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage("SELECT statement MUST be set with select() method if JOIN is used");

        $this->sql()->addJoin('some join text')->toSql();
    }
}
