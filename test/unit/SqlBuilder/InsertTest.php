<?php namespace Blade\Database\Test\Unit\SqlBuilder;

use Blade\Database\Sql\SqlBuilder;

/**
 * @see \Blade\Database\Sql\SqlBuilder
 */
class InsertTest extends \PHPUnit_Framework_TestCase
{
    use TestSqlTrait;


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
}
