<?php namespace Test\BladeDatabase;

use Blade\Database\DbAdapter;
use Blade\Database\Sql\SqlBuilder;
use Blade\Database\Test\TestDbConnection;

/**
 * @see \Blade\Database\DbAdapter
 */
class DbAdapterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Select List
     */
    public function testSelectList()
    {
        $con = new TestDbConnection();
        $db = new DbAdapter($con);

        $con->returnValues = [$rows = [
            ['id' => 1, 'name' => 'A'],
            ['id' => 2, 'name' => 'B'],
        ]];
        $result = $db->selectList($sql = 'select *');

        $this->assertEquals($rows, $result);
        $this->assertEquals([$sql], $con->log);

        // Empty search
        $con->returnValues = [];
        $result = $db->selectList($sql = 'select *');
        $this->assertSame([], $result);
    }


    /**
     * Select Row
     */
    public function testSelectRow()
    {
        $db = new DbAdapter($con = new TestDbConnection());
        $con->returnValues = [[
            $row = ['id' => 1, 'name' => 'A'],
            ['id' => 2, 'name' => 'B'],
        ]];
        $result = $db->selectRow($sql = 'select *');

        $this->assertEquals($row, $result);
        $this->assertEquals([$sql], $con->log);

        // StdClass
        $row = new \StdClass;
        $row->id = 1;
        $db = new DbAdapter($con = new TestDbConnection());
        $con->returnValues = [[$row]];
        $result = $db->selectRow($sql);
        $this->assertEquals(['id' => 1], $result);

        // Empty search
        $db = new DbAdapter($con = new TestDbConnection());
        $con->returnValues = [];
        $result = $db->selectRow($sql);
        $this->assertSame([], $result);
    }


    /**
     * Select Column
     */
    public function testSelectColumn()
    {
        $db = new DbAdapter($con = new TestDbConnection());
        $con->returnValues = [[
            $row = ['id' => 1, 'name' => 'A'],
            ['id' => 2, 'name' => 'B'],
        ]];
        $result = $db->selectColumn($sql = 'select *');

        $this->assertEquals([1, 2], $result);
        $this->assertEquals([$sql], $con->log);

        // StdClass
        $row = new \StdClass;
        $row->id = 1;
        $db = new DbAdapter($con = new TestDbConnection());
        $con->returnValues = [$row];
        $result = $db->selectColumn($sql);
        $this->assertEquals([1], $result);

        // Empty search
        $db = new DbAdapter($con = new TestDbConnection());
        $result = $db->selectColumn($sql);
        $this->assertSame([], $result);
    }


    /**
     * Select Column
     */
    public function testSelectValue()
    {
        $db = new DbAdapter($con = new TestDbConnection());
        $con->returnValues = [[
            $row = ['id' => 'a', 'name' => 'A'],
            ['id' => 2, 'name' => 'B'],
        ]];
        $result = $db->selectValue($sql = 'select *');

        $this->assertEquals('a', $result);
        $this->assertEquals([$sql], $con->log);

        // StdClass
        $row = new \StdClass;
        $row->id = 'a';
        $db = new DbAdapter($con = new TestDbConnection());
        $con->returnValues = [[$row]];
        $result = $db->selectValue($sql);
        $this->assertEquals('a', $result);

        // Empty search
        $db = new DbAdapter($con = new TestDbConnection());
        $result = $db->selectValue($sql);
        $this->assertNull($result);
    }


    /**
     * Select KeyValue
     */
    public function testSelectKeyValue()
    {
        $db = new DbAdapter($con = new TestDbConnection());
        $con->returnValues = [[
            ['id' => 1, 'name' => 'A'],
            ['id' => 2, 'name' => 'B'],
        ]];
        $result = $db->selectKeyValue($sql = 'select *');

        $this->assertEquals([1=>'A', 2=>'B'], $result);
        $this->assertEquals([$sql], $con->log);

        // StdClass
        $row = new \StdClass;
        $row->id = '1';
        $row->code = 'A';
        $db = new DbAdapter($con = new TestDbConnection());
        $con->returnValues = [[$row]];
        $result = $db->selectKeyValue($sql);
        $this->assertEquals([1=>'A'], $result);

        // Empty search
        $db = new DbAdapter($con = new TestDbConnection());
        $result = $db->selectKeyValue($sql);
        $this->assertSame([], $result);
    }


    /**
     * Transaction Commit
     */
    public function testTransactionCommit()
    {
        $con = new TestDbConnection();
        $db = new DbAdapter($con);

        $sql = 'select *';
        $result = $db->transaction(function() use ($sql, $con) {
            $con->select($sql);
        });

        $this->assertEquals(['begin', $sql, 'commit'], $con->log);
    }


    /**
     * Transaction Rollback
     */
    public function testTransactionRollback()
    {
        $con = new TestDbConnection();
        $db = new DbAdapter($con);

        $sql = 'select *';

        try {
            $result = $db->transaction(function() use ($sql, $con) {
                $con->select($sql);
                throw new \RuntimeException('sql error');
            });
            $this->fail('Excepted exception');

        } catch (\RuntimeException $e) {
            $this->assertEquals('sql error', $e->getMessage());
        }

        $this->assertEquals(['begin', $sql, 'rollback'], $con->log);
    }


    /**
     * Select List
     */
    public function testChunk()
    {
        $con = new TestDbConnection();
        $db = new DbAdapter($con);

        $con->returnValues = [
            [2],
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
