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
     * @param array  $bindings
     * @return int
     */
    public function execute($sql, array $bindings = []): int;

    /**
     * Выполнить SQL и для каждой строки выборки выкинуть yield
     * Реализовать собственную обработку ошибок выполнения запроса
     *
     * @param string $sql
     * @param array  $bindings
     * @return \Generator
     */
    public function each($sql, array $bindings = []): \Generator;


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
    public function rollback();


    /**
     * Экранирование значения для подстановки в запрос
     *
     * @param  string $value
     * @return string
     */
    public function escape($value): string;
}
