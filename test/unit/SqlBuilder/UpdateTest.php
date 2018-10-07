<?php namespace Blade\Database\Test\Unit\SqlBuilder;

use Blade\Database\Sql\SqlBuilder;

/**
 * @see \Blade\Database\Sql\SqlBuilder
 */
class UpdateTest extends \PHPUnit_Framework_TestCase
{
    use TestSqlTrait;

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
