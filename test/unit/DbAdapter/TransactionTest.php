<?php namespace Test\Blade\Database\DbAdapter;

use Blade\Database\DbAdapter;
use Blade\Database\Test\TestDbConnection;

/**
 * @see \Blade\Database\DbAdapter
 */
class TransactionTest extends \PHPUnit_Framework_TestCase
{
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
}
