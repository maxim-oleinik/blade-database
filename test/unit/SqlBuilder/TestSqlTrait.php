<?php namespace Blade\Database\Test\Unit\SqlBuilder;

use Blade\Database\Sql\SqlBuilder;

trait TestSqlTrait
{
    protected $table = 'some_table_name';

    /**
     * @param null $label
     * @return SqlBuilder
     */
    public function sql($label = null)
    {
        return (new SqlBuilder($label))->from($this->table, 't');
    }
}
