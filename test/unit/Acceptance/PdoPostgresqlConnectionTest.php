<?php namespace Test\Blade\Database\Acceptance;

use Blade\Database\Connection\PdoConnection;

/**
 * @see \Blade\Database\DbAdapter
 */
class PdoPostgresqlConnectionTest extends BaseAcceptanceScenarioTest
{
    public function testAll()
    {
        if (!defined('TESTS_BLADE_DB_POSTGRES_ENABLED') || !TESTS_BLADE_DB_POSTGRES_ENABLED) {
            $this->markTestSkipped('PostgresSQL tests are disabled!');
        }

        $dsn = sprintf('pgsql:host=%s;port=%d;dbname=%s', TESTS_BLADE_DB_POSTGRES_HOST, TESTS_BLADE_DB_POSTGRES_PORT, TESTS_BLADE_DB_POSTGRES_DATABASE);
        $user = TESTS_BLADE_DB_POSTGRES_USERNAME;
        $pass = TESTS_BLADE_DB_POSTGRES_PASSWORD;
        $connection = new PdoConnection($dsn, $user, $pass, [
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
        ]);

        $this->runScenario($connection);
    }
}
