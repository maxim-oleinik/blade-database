<?php namespace Test\Blade\Database\Acceptance;

use Blade\Database\Connection\PostgresConnection;

/**
 * @see \Blade\Database\DbAdapter
 */
class PostgresqlConnectionTest extends BaseAcceptanceScenarioTest
{
    public function testAll()
    {
        if (!defined('TESTS_BLADE_DB_POSTGRES_ENABLED') || !TESTS_BLADE_DB_POSTGRES_ENABLED) {
            $this->markTestSkipped('PostgresSQL tests are disabled!');
        }

        $dsn = sprintf('host=%s port=%d dbname=%s user=%s password=%s',
            TESTS_BLADE_DB_POSTGRES_HOST,
            TESTS_BLADE_DB_POSTGRES_PORT,
            TESTS_BLADE_DB_POSTGRES_DATABASE,
            TESTS_BLADE_DB_POSTGRES_USERNAME,
            TESTS_BLADE_DB_POSTGRES_PASSWORD
        );
        $connection = new PostgresConnection($dsn, PGSQL_CONNECT_FORCE_NEW);

        $this->runScenario($connection);
    }
}
