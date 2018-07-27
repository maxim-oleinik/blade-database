<?php namespace Test\Blade\Database\DbAdapter;

use Blade\Database\DbAdapter;
use Blade\Database\Sql\SqlBuilder;
use Blade\Database\Connection\TestStubDbConnection;

/**
 * @see \Blade\Database\DbAdapter
 */
class ChunkTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Select List
     */
    public function testChunk()
    {
        $con = new TestStubDbConnection();
        $db = new DbAdapter($con);

        $con->addReturnResultSet([[2]]);
        $con->addReturnResultSet($rows1 = [['id' => 1, 'name' => 'A']]);
        $con->addReturnResultSet($rows2 = [['id' => 2, 'name' => 'B']]);

        $sql = (new SqlBuilder())->from('table');
        $calls = [];
        $db->chunk(1, $sql, function ($rows) use (&$calls) {
            $calls[] = $rows;
        });

        $this->assertEquals($calls, [$rows1, $rows2]);

        $this->assertEquals([
            "SELECT count(*)\nFROM table",
            "SELECT *\nFROM table\nLIMIT 1",
            "SELECT *\nFROM table\nLIMIT 1 OFFSET 1",
        ], $con->log);
    }
}
