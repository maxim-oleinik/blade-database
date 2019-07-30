<?php namespace Blade\Database\Test\Unit\SqlBuilder;

use Blade\Database\Sql\SqlBuilder;

/**
 * @see \Blade\Database\Sql\SqlBuilder
 */
class HooksTest extends \PHPUnit\Framework\TestCase
{
    use TestSqlTrait;

    /**
     * LABEL
     */
    public function testOnSelectEvent()
    {
        $sql = $this->sql()
            ->onBuildWhere(function (SqlBuilder $sql) {
                $sql->andWhere('abc=1');
            });

        $this->assertEquals("SELECT *\nFROM {$this->table} AS t\nWHERE abc=1", $sql->toSql());
        // Повторный вызов никак не влияет на результат
        $this->assertEquals("SELECT *\nFROM {$this->table} AS t\nWHERE abc=1", $sql->toSql());
        $this->assertNotContains("abc=1", $sql->insert()->toSql());
        $this->assertNotContains("abc=1", $sql->delete()->toSql());
        $this->assertNotContains("abc=1", $sql->update()->toSql());
    }
}
