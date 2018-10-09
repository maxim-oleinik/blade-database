<?php namespace Blade\Database\Test\Unit\SqlBuilder;

/**
 * @see \Blade\Database\Sql\SqlBuilder
 */
class SelectTest extends \PHPUnit_Framework_TestCase
{
    use TestSqlTrait;

    /**
     * SELECT
     */
    public function testSelect()
    {
        $sql = $this->sql()
            ->addSelect('col1, col2')
            ->addSelect('col3');
        $this->assertEquals("SELECT col1, col2, col3\nFROM {$this->table} AS t", $sql->toSql());

        $sql->select('col');
        $this->assertEquals("SELECT col\nFROM {$this->table} AS t", $sql->toSql());
    }


    /**
     * SELECT count() GROUP BY
     */
    public function testCountWithGroupBy()
    {
        $sql = $this->sql('label')
            ->from('contacts')
            ->select('status, count(*)')
            ->groupBy('status')
            ->having('1=1');
        $alias = 't'.md5($sql);
        $sql = $sql->orderBy('status')->count();

        $this->assertEquals("/*label*/".PHP_EOL.
            "SELECT count(*)".PHP_EOL
            ."FROM (SELECT status, count(*)".PHP_EOL.
            "FROM contacts".PHP_EOL.
            "GROUP BY status".PHP_EOL.
            "HAVING 1=1) AS {$alias}",
            $sql->toSql()
        );
    }
}
