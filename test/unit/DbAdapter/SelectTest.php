<?php namespace Test\Blade\Database\DbAdapter;

use Blade\Database\DbAdapter;
use Blade\Database\Connection\TestStubDbConnection;

/**
 * @see \Blade\Database\DbAdapter
 */
class SelectTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Select List
     */
    public function testSelectList()
    {
        $con = new TestStubDbConnection();
        $db = new DbAdapter($con);

        $con->addReturnResultSet($rows = [
            ['id' => 1, 'name' => 'A'],
            ['id' => 2, 'name' => 'B'],
        ]);
        $result = $db->selectAll($sql = 'select *');

        $this->assertEquals($rows, $result);
        $this->assertEquals([$sql], $con->log);

        // Empty search
        $con->returnValues = [];
        $result = $db->selectAll($sql = 'select *');
        $this->assertSame([], $result);
    }


    /**
     * Select Row
     */
    public function testSelectRow()
    {
        $db = new DbAdapter($con = new TestStubDbConnection());
        $con->addReturnResultSet([
            $row = ['id' => 1, 'name' => 'A'],
            ['id' => 2, 'name' => 'B'],
        ]);
        $result = $db->selectRow($sql = 'select *');

        $this->assertEquals($row, $result);
        $this->assertEquals([$sql], $con->log);

        // StdClass
        $row = new \StdClass;
        $row->id = 1;
        $db = new DbAdapter($con = new TestStubDbConnection());
        $con->addReturnResultSet([$row]);
        $result = $db->selectRow($sql);
        $this->assertEquals(['id' => 1], $result);

        // Empty search
        $db = new DbAdapter($con = new TestStubDbConnection());
        $con->returnValues = [];
        $result = $db->selectRow($sql);
        $this->assertSame([], $result);
    }


    /**
     * Select Column
     */
    public function testSelectColumn()
    {
        $db = new DbAdapter($con = new TestStubDbConnection());
        $con->addReturnResultSet([
            $row = ['id' => 1, 'name' => 'A'],
            ['id' => 2, 'name' => 'B']
        ]);
        $result = $db->selectColumn($sql = 'select *');

        $this->assertEquals([1, 2], $result);
        $this->assertEquals([$sql], $con->log);

        // StdClass
        $row = new \StdClass;
        $row->id = 1;
        $db = new DbAdapter($con = new TestStubDbConnection());
        $con->addReturnResultSet([$row]);
        $result = $db->selectColumn($sql);
        $this->assertEquals([1], $result);

        // Empty search
        $db = new DbAdapter($con = new TestStubDbConnection());
        $result = $db->selectColumn($sql);
        $this->assertSame([], $result);
    }


    /**
     * Select Column
     */
    public function testSelectValue()
    {
        $db = new DbAdapter($con = new TestStubDbConnection());
        $con->addReturnResultSet([
            $row = ['id' => 'a', 'name' => 'A'],
            ['id' => 2, 'name' => 'B'],
        ]);
        $result = $db->selectValue($sql = 'select *');

        $this->assertEquals('a', $result);
        $this->assertEquals([$sql], $con->log);

        // StdClass
        $row = new \StdClass;
        $row->id = 'a';
        $db = new DbAdapter($con = new TestStubDbConnection());
        $con->addReturnResultSet([$row]);
        $result = $db->selectValue($sql);
        $this->assertEquals('a', $result);

        // Empty search
        $db = new DbAdapter($con = new TestStubDbConnection());
        $result = $db->selectValue($sql);
        $this->assertFalse($result);
    }


    /**
     * Select KeyValue
     */
    public function testSelectKeyValue()
    {
        $db = new DbAdapter($con = new TestStubDbConnection());
        $con->addReturnResultSet([
            ['id' => 1, 'name' => 'A'],
            ['id' => 2, 'name' => 'B'],
        ]);
        $result = $db->selectKeyValue($sql = 'select *');

        $this->assertEquals([1=>'A', 2=>'B'], $result);
        $this->assertEquals([$sql], $con->log);

        // StdClass
        $row = new \StdClass;
        $row->id = '1';
        $row->code = 'A';
        $db = new DbAdapter($con = new TestStubDbConnection());
        $con->addReturnResultSet([$row]);
        $result = $db->selectKeyValue($sql);
        $this->assertEquals([1=>'A'], $result);

        // Empty search
        $db = new DbAdapter($con = new TestStubDbConnection());
        $result = $db->selectKeyValue($sql);
        $this->assertSame([], $result);
    }
}
