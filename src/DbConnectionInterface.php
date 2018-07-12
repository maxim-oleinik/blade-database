<?php namespace Blade\Database;

/**
 * Базовый интерфейс для работы с БД
 */
interface DbConnectionInterface
{
    /**
     * Выполнить запрос и вернуть кол-во затронутых строк.
     * Реализовать собственную обработку ошибок выполнения запроса
     *
     * @param string $sql
     * @param array $bindings
     *
     * @return int
     */
    public function execute($sql, $bindings = []): int;

    /**
     * Выполнить SQL и для каждой строки выборки вызвать указанную callback-функцию
     *     callback принимает строку ТОЛЬКО как МАССИВ
     * Реализовать собственную обработку ошибок выполнения запроса
     *
     * @param string   $sql
     * @param array    $bindings
     * @param callable $callback
     */
    public function each($sql, $bindings = [], callable $callback);


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
