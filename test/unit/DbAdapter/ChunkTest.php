<?php namespace Test\Blade\Database\DbAdapter;

use Blade\Database\DbAdapter;
use Blade\Database\Sql\SqlBuilder;
use Blade\Database\Test\TestDbConnection;

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
        $con = new TestDbConnection();
        $db = new DbAdapter($con);

        $con->returnValues = [
            [[2]],
            $rows1 = [['id' => 1, 'name' => 'A']],
            $rows2 = [['id' => 2, 'name' => 'B']],
        ];

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
