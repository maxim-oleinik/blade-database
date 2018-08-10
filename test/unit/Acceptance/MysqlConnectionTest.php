<?php namespace Test\Blade\Database\Acceptance;

use Blade\Database\Connection\MysqlConnection;

require_once __DIR__ . '/BaseAcceptanceScenarioTest.php';

/**
 * @see \Blade\Database\DbAdapter
 */
class MysqlConnectionTest extends BaseAcceptanceScenarioTest
{
    protected $allowDdlTransactions = false;

    public function testAll()
    {
        if (!defined('TESTS_BLADE_DB_MYSQL_ENABLED') || !TESTS_BLADE_DB_MYSQL_ENABLED) {
            $this->markTestSkipped('MySQL tests are disabled!');
        }

        $connection = new MysqlConnection(
            TESTS_BLADE_DB_MYSQL_HOST,
            TESTS_BLADE_DB_MYSQL_USERNAME,
            TESTS_BLADE_DB_MYSQL_PASSWORD,
            TESTS_BLADE_DB_MYSQL_DATABASE,
            TESTS_BLADE_DB_MYSQL_PORT
        );

        $this->runScenario($connection);
    }
}
