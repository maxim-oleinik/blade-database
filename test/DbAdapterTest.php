<?php namespace Test\BladeDatabase;

use Blade\Database\DbAdapter;
use Blade\Database\Test\TestDbConnection;

class DbAdapterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Select List
     */
    public function testSelectList()
    {
        $con = new TestDbConnection();
        $db = new DbAdapter($con);

        $con->returnValue = [
            ['id' => 1, 'name' => 'A'],
            ['id' => 2, 'name' => 'B'],
        ];
        $result = $db->selectList($sql = 'select *');

        $this->assertEquals($con->returnValue, $result);
        $this->assertEquals([$sql], $con->log);

        // Empty search
        $con->returnValue = false;
        $result = $db->selectList($sql = 'select *');
        $this->assertSame([], $result);
    }


    /**
     * Select Row
     */
    public function testSelectRow()
    {
        $con = new TestDbConnection();
        $db = new DbAdapter($con);

        $con->returnValue = [
            $row = ['id' => 1, 'name' => 'A'],
            ['id' => 2, 'name' => 'B'],
        ];
        $result = $db->selectRow($sql = 'select *');

        $this->assertEquals($row, $result);
        $this->assertEquals([$sql], $con->log);

        // StdClass
        $row = new \StdClass;
        $row->id = 1;
        $con->returnValue = [$row];
        $result = $db->selectRow($sql);
        $this->assertEquals(['id' => 1], $result);

        // Empty search
        $con->returnValue = false;
        $result = $db->selectRow($sql);
        $this->assertSame([], $result);
    }


    /**
     * Select Column
     */
    public function testSelectColumn()
    {
        $con = new TestDbConnection();
        $db = new DbAdapter($con);

        $con->returnValue = [
            $row = ['id' => 1, 'name' => 'A'],
            ['id' => 2, 'name' => 'B'],
        ];
        $result = $db->selectColumn($sql = 'select *');

        $this->assertEquals([1, 2], $result);
        $this->assertEquals([$sql], $con->log);

        // StdClass
        $row = new \StdClass;
        $row->id = 1;
        $con->returnValue = [$row];
        $result = $db->selectColumn($sql);
        $this->assertEquals([1], $result);

        // Empty search
        $con->returnValue = false;
        $result = $db->selectColumn($sql);
        $this->assertSame([], $result);
    }


    /**
     * Select Column
     */
    public function testSelectValue()
    {
        $con = new TestDbConnection();
        $db = new DbAdapter($con);

        $con->returnValue = [
            $row = ['id' => 'a', 'name' => 'A'],
            ['id' => 2, 'name' => 'B'],
        ];
        $result = $db->selectValue($sql = 'select *');

        $this->assertEquals('a', $result);
        $this->assertEquals([$sql], $con->log);

        // StdClass
        $row = new \StdClass;
        $row->id = 'a';
        $con->returnValue = [$row];
        $result = $db->selectValue($sql);
        $this->assertEquals('a', $result);

        // Empty search
        $con->returnValue = false;
        $result = $db->selectValue($sql);
        $this->assertNull($result);
    }


    /**
     * Select KeyValue
     */
    public function testSelectKeyValue()
    {
        $con = new TestDbConnection();
        $db = new DbAdapter($con);

        $con->returnValue = [
            ['id' => 1, 'name' => 'A'],
            ['id' => 2, 'name' => 'B'],
        ];
        $result = $db->selectKeyValue($sql = 'select *');

        $this->assertEquals([1=>'A', 2=>'B'], $result);
        $this->assertEquals([$sql], $con->log);

        // StdClass
        $row = new \StdClass;
        $row->id = '1';
        $row->code = 'A';
        $con->returnValue = [$row];
        $result = $db->selectKeyValue($sql);
        $this->assertEquals([1=>'A'], $result);

        // Empty search
        $con->returnValue = false;
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
            $con->query($sql);
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
                $con->query($sql);
                throw new \RuntimeException('sql error');
            });
            $this->fail('Excepted exception');

        } catch (\RuntimeException $e) {
            $this->assertEquals('sql error', $e->getMessage());
        }

        $this->assertEquals(['begin', $sql, 'rollback'], $con->log);
    }
}
