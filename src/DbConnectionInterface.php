<?php namespace Blade\Database;

/**
 * Базовый интерфейс для работы с БД
 */
interface DbConnectionInterface
{
    /**
     * Выполнить запрос
     *
     * @param string $sql
     * @param array $bindings
     * @return bool|int
     */
    public function execute($sql, $bindings = []);

    /**
     * Выполнить запрос и вернуть структуру доступную для foreach
     * или bool, если запрос не предполагает возврат значений
     *
     * @param string $sql
     * @param array $bindings
     * @return bool|array
     */
    public function select($sql, $bindings = []);

    /**
     * Start a new database transaction.
     *
     * @return void
     */
    public function beginTransaction();

    /**
     * Commit the active database transaction.
     *
     * @return void
     */
    public function commit();

    /**
     * Rollback the active database transaction.
     *
     * @return void
     */
    public function rollBack();
}
