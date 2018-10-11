<?php namespace Blade\Database\Test\Unit\SqlBuilder;

use Blade\Database\Sql\SqlBuilder;

/**
 * @see \Blade\Database\Sql\SqlBuilder
 */
class WhereTest extends \PHPUnit_Framework_TestCase
{
    use TestSqlTrait;

    /**
     * WHERE
     */
    public function testWhere()
    {
        $sql = $this->sql();
        $sql->andWhere($a = 'col1=123');
        $sql->andWhere($b = 'col2="123" AND col3=4');
        $this->assertEquals("SELECT *\nFROM {$this->table} AS t\nWHERE {$a} AND {$b}", $sql->toSql());
    }


    /**
     * WHERE Equals
     */
    public function testWhereEquals()
    {
        $sql = $this->sql();
        $sql->andWhereEquals('col1', 'text');
        $this->assertEquals("SELECT *\nFROM {$this->table} AS t\nWHERE col1='text'", $sql->toSql());

        $sql = $this->sql();
        $sql->andWhereEquals('col1', '');
        $this->assertEquals("SELECT *\nFROM {$this->table} AS t\nWHERE col1=''", $sql->toSql());

        $sql = $this->sql();
        $sql->andWhereEquals('col1', null);
        $this->assertEquals("SELECT *\nFROM {$this->table} AS t\nWHERE col1 IS NULL", $sql->toSql());
    }


    /**
     * WHERE IN
     */
    public function testWhereIn()
    {
        $sql = $this->sql();
        $sql->andWhereIn('col', ['a', "'b"]);
        $this->assertEquals("SELECT *\nFROM {$this->table} AS t\nWHERE col IN ('a', '''b')", $sql->toSql());
    }


    /**
     * WHERE NOT IN
     */
    public function testWhereNotIn()
    {
        $sql = $this->sql();
        $sql->andWhereNotIn('t.col', [1,2]);
        $this->assertEquals("SELECT *\nFROM {$this->table} AS t\nWHERE t.col NOT IN ('1', '2')", $sql->toSql());
    }


    /**
     * WHERE IN (SELECT ...)
     */
    public function testWhereInSelect()
    {
        $subSql = SqlBuilder::make()->from('sub_table', 'sub')->andWhereEquals('type', 1)->select('id');

        $sql = $this->sql();
        $sql->andWhereIn('col', $subSql);
        $this->assertEquals("SELECT *\nFROM {$this->table} AS t\nWHERE col IN (SELECT id\nFROM sub_table AS sub\nWHERE type='1')", $sql->toSql());

        $sql = $this->sql();
        $sql->andWhereNotIn('col', $subSql);
        $this->assertEquals("SELECT *\nFROM {$this->table} AS t\nWHERE col NOT IN (SELECT id\nFROM sub_table AS sub\nWHERE type='1')", $sql->toSql());
    }


    /**
     * andWhere sprintf
     */
    public function testWhereSprintf()
    {
        $sql = $this->sql();
        $sql->andWhere("colA=%d AND colC='%s'", $a = '21.21', 'text');
        $sql->andWhere("colB='%s'", $b = "'B\"");
        $this->assertEquals("SELECT *\nFROM {$this->table} AS t\nWHERE colA=21 AND colC='text' AND colB='''B\"'", $sql->toSql());
    }


    /**
     * OR
     */
    public function testOrWhere()
    {
        $sql = $this->sql();
        $sql->andWhere($a = 'colA=123');
        $sql->orWhere($b = 'colB=%d AND col3=4', $bVal = 21);
        $this->assertEquals("SELECT *\nFROM {$this->table} AS t\nWHERE {$a} OR " . sprintf($b, $bVal), $sql->toSql());
    }

    /**
     * OR exception
     */
    public function testFirstOrException()
    {
        $sql = $this->sql();
        $this->setExpectedException('InvalidArgumentException', 'Invalid first OR condition');
        $sql->orWhere('a=1');
    }
}
