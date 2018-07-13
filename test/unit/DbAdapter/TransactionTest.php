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
            $con->execute($sql);
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
                $con->execute($sql);
                throw new \RuntimeException('sql error');
            });
            $this->fail('Excepted exception');

        } catch (\RuntimeException $e) {
            $this->assertEquals('sql error', $e->getMessage());
        }

        $this->assertEquals(['begin', $sql, 'rollback'], $con->log);
    }


    /**
     * Вложенные транзакции
     */
    public function testNestTransactions()
    {
        $con = new TestDbConnection();
        $db = new DbAdapter($con);

        $this->assertSame(1, $db->beginTransaction());
        $this->assertSame(2, $db->beginTransaction()); // sp1
        $this->assertSame(3, $db->beginTransaction()); // sp2
        $this->assertSame(2, $db->commit()); // sp2
        $this->assertSame(1, $db->rollBack()); // sp1
        $this->assertSame(0, $db->rollBack());

        $this->assertEquals([
            'begin',
            'SAVEPOINT sp1',
            'SAVEPOINT sp2',
            'RELEASE SAVEPOINT sp2',
            'ROLLBACK TO SAVEPOINT sp1',
            'rollback',
        ], $con->log);
    }


    /**
     * Rollback Force
     */
    public function testRollbackForce()
    {
        $con = new TestDbConnection();
        $db = new DbAdapter($con);

        $this->assertSame(1, $db->beginTransaction());
        $this->assertSame(2, $db->beginTransaction());
        $this->assertSame(3, $db->beginTransaction());
        $this->assertSame(2, $db->rollBack());
        $this->assertSame(0, $db->rollBack(true));

        $this->assertEquals([
            'begin',
            'SAVEPOINT sp1',
            'SAVEPOINT sp2',
            'ROLLBACK TO SAVEPOINT sp2',
            'rollback',
        ], $con->log);
    }
}
