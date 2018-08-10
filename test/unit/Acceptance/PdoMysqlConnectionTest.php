<?php namespace Test\Blade\Database\Acceptance;

use Blade\Database\Connection\PdoConnection;

/**
 * @see \Blade\Database\DbAdapter
 */
class PdoMysqlConnectionTest extends BaseAcceptanceScenarioTest
{
    protected $allowDdlTransactions = false;

    public function testAll()
    {
        if (!defined('TESTS_BLADE_DB_MYSQL_ENABLED') || !TESTS_BLADE_DB_MYSQL_ENABLED) {
            $this->markTestSkipped('MySQL tests are disabled!');
        }

        $dsn = sprintf('mysql:host=%s;port=%d;dbname=%s', TESTS_BLADE_DB_MYSQL_HOST, TESTS_BLADE_DB_MYSQL_PORT, TESTS_BLADE_DB_MYSQL_DATABASE);
        $user = TESTS_BLADE_DB_MYSQL_USERNAME;
        $pass = TESTS_BLADE_DB_MYSQL_PASSWORD;
        $connection = new PdoConnection($dsn, $user, $pass, [
            \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
        ]);

        $this->runScenario($connection);
    }
}
