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
     * Выполнить SQL и для каждой строки выборки вызвать указанную callback-функцию
     *     callback принимает строку ТОЛЬКО как МАССИВ
     * Реализовать собственную обработку ошибок выполнения запроса
     *
     * @param string   $sql
     * @param callable $callback
     * @param array    $bindings
     */
    public function each($sql, callable $callback, array $bindings = []);


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
