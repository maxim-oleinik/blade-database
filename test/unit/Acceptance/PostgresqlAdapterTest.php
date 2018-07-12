<?php namespace Test\Blade\Database\Acceptance;
use Blade\Database\DbAdapter;
use Blade\Database\Sql\SqlBuilder;
use Blade\Database\Test\PdoConnection;

defined('TESTS_BLADE_DB_POSTGRES_HOST') || define('TESTS_BLADE_DB_POSTGRES_HOST', getenv('TESTS_BLADE_DB_POSTGRES_HOST'));
defined('TESTS_BLADE_DB_POSTGRES_USERNAME') || define('TESTS_BLADE_DB_POSTGRES_USERNAME', getenv('TESTS_BLADE_DB_POSTGRES_USERNAME'));
defined('TESTS_BLADE_DB_POSTGRES_PASSWORD') || define('TESTS_BLADE_DB_POSTGRES_PASSWORD', getenv('TESTS_BLADE_DB_POSTGRES_PASSWORD'));
defined('TESTS_BLADE_DB_POSTGRES_DATABASE') || define('TESTS_BLADE_DB_POSTGRES_DATABASE', getenv('TESTS_BLADE_DB_POSTGRES_DATABASE'));
defined('TESTS_BLADE_DB_POSTGRES_PORT') || define('TESTS_BLADE_DB_POSTGRES_PORT', getenv('TESTS_BLADE_DB_POSTGRES_PORT'));


/**
 * @see \Blade\Database\DbAdapter
 */
class PostgresqlAdapterTest extends \PHPUnit_Framework_TestCase
{
    public function testAll()
    {
        if (!defined('TESTS_BLADE_DB_POSTGRES_ENABLED') || !TESTS_BLADE_DB_POSTGRES_ENABLED) {
            $this->markTestSkipped('PostgresSQL tests are disabled!');
        }

        $dsn  = sprintf('pgsql:host=%s;port=%d;dbname=%s',
            TESTS_BLADE_DB_POSTGRES_HOST,
            TESTS_BLADE_DB_POSTGRES_PORT,
            TESTS_BLADE_DB_POSTGRES_DATABASE
            );
        $user = TESTS_BLADE_DB_POSTGRES_USERNAME;
        $pass = TESTS_BLADE_DB_POSTGRES_PASSWORD;
        $connection = new PdoConnection($dsn, $user, $pass, [
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
        ]);

        $db = new DbAdapter($connection);
        try {
            $db->beginTransaction();

            $tableName = 'tmp_blade_test';
            $baseSql = SqlBuilder::make()->from($tableName);
            $db->execute("
                CREATE TABLE {$tableName}
                (
                  id INT NOT NULL,
                  code text,
                  CONSTRAINT {$tableName}_pkey PRIMARY KEY (id)
                )
            ");

            /**
             * Excecute - insert, update
             */
            $sql = $baseSql->copy()->insert()
                ->batchMode(true)
                ->values([
                    ['id'=>1, 'code'=>'A'],
                    ['id'=>2, 'code'=>'B'],
                ]);
            $this->assertEquals(2, $db->execute($sql));

            $sql = $baseSql->copy()->update()
                ->values(['code' => 'AA'])
                ->andWhere('id=%d', 1);
            $this->assertEquals(1, $db->execute($sql));

            /**
             * Select Value
             */
            $sql = $baseSql->copy()
                ->andWhere('id=1')
                ->select('code, id');
            $this->assertEquals('AA', $db->selectValue($sql));

            /**
             * Select KeyValue
             */
            $sql = $baseSql->copy()
                ->select('code, id');
            $this->assertEquals(['AA'=>1, 'B'=>2], $db->selectKeyValue($sql));

            /**
             * Select Column
             */
            $sql = $baseSql->copy()
                ->select('code, id')
                ->orderBy('id');
            $this->assertEquals(['AA', 'B'], $db->selectColumn($sql));

            /**
             * Select Row
             */
            $sql = $baseSql->copy()
                ->andWhere('id=1');
            $this->assertEquals(['id'=>1, 'code'=>'AA'], $db->selectRow($sql));

            /**
             * Select List
             */
            $sql = $baseSql->copy()
                ->orderBy('id');
            $this->assertEquals([
                ['id'=>1, 'code'=>'AA'],
                ['id'=>2, 'code'=>'B'],
            ], $db->selectList($sql));


            /**
             * Transaction
             */

            // Откат
            $insertSql = $baseSql->copy()->insert()->values(['id'=>3, 'code'=>'C']);
            $findInsertedSql = $baseSql->copy()->andWhere('id=3');
            $errMess = __METHOD__;
            try {
                $db->transaction(function () use ($db, $tableName, $insertSql, $errMess) {
                    $db->execute($insertSql->copy()->values(['id'=>4, 'code'=>'D']));
                    // Abort transaction
                    throw new \Exception($errMess);
                });
                $this->fail('Expected Exception');
            } catch (\Exception $e) {
                if ($e->getMessage() != $errMess) {
                    throw $e;
                }
                unset($e);
            }

            // Транзакцию накатили, изменений нет
            $this->assertSame([], $db->selectList($findInsertedSql));
            $this->assertSame([], $db->selectRow($findInsertedSql));
            $this->assertSame([], $db->selectColumn($findInsertedSql));
            $this->assertSame([], $db->selectKeyValue($findInsertedSql));
            $this->assertFalse($db->selectValue($findInsertedSql));

            // Успешная транзакция
            $db->transaction(function () use ($db, $tableName, $insertSql) {
                $db->execute($insertSql);
            });
            $this->assertTrue((bool)$db->selectValue($findInsertedSql));

            /**
             * Nested Transactions
             */
            $db->beginTransaction();
            $db->beginTransaction(); // sp1
            $db->beginTransaction(); // sp2
                $db->execute($insertSql->values(['id'=>4, 'code'=>'D']));
            $db->commit(); // sp2
                // Запись закоммичена и найдена
                $this->assertTrue((bool)$db->selectValue($baseSql->copy()->andWhere('id=4')));
            $db->rollBack(); // sp1
                // Откат, записи нет
                $this->assertFalse((bool)$db->selectValue($baseSql->copy()->andWhere('id=4')));
            $db->rollBack();

        } catch (\Exception $e) {}

        if (isset($e)) {
            throw $e;
        }

        $db->rollBack();
    }
}