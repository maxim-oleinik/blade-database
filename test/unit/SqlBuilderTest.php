<?php namespace Test\Blade\Database;

use Blade\Database\Sql\SqlBuilder;

/**
 * @see \Blade\Database\Sql\SqlBuilder
 */
class SqlBuilderTest extends \PHPUnit_Framework_TestCase
{
    private $table = 'some_table_name';

    /**
     * @param null $label
     * @return SqlBuilder
     */
    private function _sql($label = null)
    {
        return (new SqlBuilder($label))->from($this->table);
    }


    /**
     * LABEL
     */
    public function testLabel()
    {
        $sql = $this->_sql($label = "abc");
        $this->assertEquals("/*{$label}*/\nSELECT *\nFROM ".$this->table, $sql->toSql());

        $sql->setLabel($label = __METHOD__);
        $this->assertEquals("/*{$label}*/\nSELECT *\nFROM ".$this->table, $sql->toSql());
    }


    /**
     * FROM
     */
    public function testFrom()
    {
        $sql = (new SqlBuilder)
            ->from($this->table, 't');

        $this->assertEquals("SELECT *\nFROM {$this->table} AS t", $sql->toSql());
    }


    /**
     * SELECT
     */
    public function testSelect()
    {
        $sql = $this->_sql()
            ->addSelect('col1, col2')
            ->addSelect('col3');
        $this->assertEquals("SELECT col1, col2, col3\nFROM ".$this->table, $sql->toSql());

        $sql->select('col');
        $this->assertEquals("SELECT col\nFROM ".$this->table, $sql->toSql());
    }


    /**
     * WHERE
     */
    public function testWhere()
    {
        $sql = $this->_sql();
        $sql->andWhere($a = 'col1=123');
        $sql->andWhere($b = 'col2="123" AND col3=4');
        $this->assertEquals("SELECT *\nFROM {$this->table}\nWHERE {$a} AND {$b}", $sql->toSql());
    }

    public function testWhereIn()
    {
        $sql = $this->_sql();
        $sql->andWhereIn('col', ['a', "'b"]);
        $this->assertEquals("SELECT *\nFROM {$this->table}\nWHERE col IN ('a', '''b')", $sql->toSql());
    }

    public function testWhereNotIn()
    {
        $sql = $this->_sql();
        $sql->andWhereNotIn('col', [1,2]);
        $this->assertEquals("SELECT *\nFROM {$this->table}\nWHERE col NOT IN ('1', '2')", $sql->toSql());
    }

    public function testWhereSprintf()
    {
        $sql = $this->_sql();
        $sql->andWhere("colA=%d AND colC='%s'", $a='21.21', 'text');
        $sql->andWhere("colB='%s'", $b="'B\"");
        $this->assertEquals("SELECT *\nFROM {$this->table}\nWHERE colA=21 AND colC='text' AND colB='''B\"'", $sql->toSql());
    }

    public function testOrWhere()
    {
        $sql = $this->_sql();
        $sql->andWhere($a = 'colA=123');
        $sql->orWhere($b='colB=%d AND col3=4', $bVal = 21);
        $this->assertEquals("SELECT *\nFROM {$this->table}\nWHERE {$a} OR ".sprintf($b, $bVal), $sql->toSql());
    }

    public function testFirstOrException()
    {
        $sql = $this->_sql();
        $this->setExpectedException('InvalidArgumentException', 'Invalid first OR condition');
        $sql->orWhere('a=1');
    }


    /**
     * ORDER BY
     */
    public function testOrder()
    {
        $sql = $this->_sql();
        $sql->addOrder($a = 'col1');
        $sql->addOrder($b = 'col2 DESC');
        $this->assertEquals("SELECT *\nFROM {$this->table}\nORDER BY {$a}, {$b}", $sql->toSql());
    }


    /**
     * LIMIT ... OFFSET
     */
    public function testLimit()
    {
        $sql = $this->_sql();
        $sql->limit(10);
        $this->assertEquals("SELECT *\nFROM {$this->table}\nLIMIT 10", $sql->toSql());
    }


    /**
     * JOIN
     */
    public function testJoin()
    {
        $sql = $this->_sql()
            ->addJoin($t2 = 'INNER JOIN table2 AS t2 USING (col)')
            ->addJoin($t3 = 'LEFT JOIN table3 AS t3 USING (col)');
        $this->assertEquals("SELECT *\nFROM {$this->table}\n{$t2}\n{$t3}", $sql->toSql());
    }

    /**
     * JOIN - Merge SQL
     */
    public function testJoinMerge()
    {
        $sql2 = SqlBuilder::make('some label should be ignored')
            ->from('table2', 't2')
            ->andWhere('t2.col=123');

        $sql = $this->_sql()->setFromAlias('t1')
            ->innerJoin($sql2, 'ON t2.id=t1.id')
            ->leftJoin($sql2, 'ON t2.col=t1.col')
            ->rightJoin($sql2)
            ->andWhere('t1.col=55');

        $this->assertEquals("SELECT *\nFROM {$this->table} AS t1\n".
            "INNER JOIN table2 AS t2 ON t2.id=t1.id\n" .
            "LEFT JOIN table2 AS t2 ON t2.col=t1.col\n" .
            "RIGHT JOIN table2 AS t2\n".
            "WHERE t2.col=123 AND t2.col=123 AND t2.col=123 AND t1.col=55", $sql->toSql());
    }


    /**
     * ALL Select
     */
    public function testComplete()
    {
        $sql = $this->_sql($label = 'label')
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

    public function testCountWithGroupBy()
    {
        $sql = $this->_sql('label')
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
            $sql->toSql());
    }


    /**
     * INSERT
     */
    public function testInsert()
    {
        $sql = (new SqlBuilder())->insert($this->table)
            ->values($values = [
                'col_int' => 23,
                'col_float' => 1.56,
                'col_str' => "val'/*",
                'col_bool' => false,
                'col_null' => null,
            ]);
        $this->assertEquals(sprintf("INSERT INTO {$this->table} (%s) VALUES (23, 1.56, 'val''/*', 0, NULL)", implode(', ', array_keys($values))), $sql->toSql());
    }

    /**
     * INSERT many
     */
    public function testInsertMany()
    {
        $sql = (new SqlBuilder())->insert($this->table)
            ->batchMode()
            ->values([[
                'id' => 1,
                'name' => 'name1',
            ],[
                'id' => 2,
                'name' => 'name2',
            ]]);
        $this->assertEquals("INSERT INTO {$this->table} (id, name) VALUES (1, 'name1'), (2, 'name2')", $sql->toSql());
    }


    /**
     * INSERT RETURNING
     */
    public function testInsertWithReturn()
    {
        $sql = (new SqlBuilder())->from($this->table)->insert()
            ->values(['col' => 23])
            ->returning($sqlPart = 'any sql part');
        $this->assertEquals("INSERT INTO {$this->table} (col) VALUES (23) RETURNING ".$sqlPart, $sql->toSql());
    }


    /**
     * UPDATE
     */
    public function testUpdate()
    {
        $sql = (new SqlBuilder())->update($this->table)
            ->andWhere('colA=1')
            ->values($values = [
                'col_int' => 23,
                'col_float' => 1.56,
                'col_str' => "val'/*",
                'col_bool' => true,
                'col_null' => null,
            ]);
        $this->assertEquals("UPDATE {$this->table} SET col_int=23, col_float=1.56, col_str='val''/*', col_bool=1, col_null=NULL\nWHERE colA=1", $sql->toSql());
    }
}
