Blade Database Adapter
======================

Универсальный адаптер к любому уже реализованному коннекту к БД.

Install
-------
1. Add to `composer.json`

2. Implement `\Blade\Database\DbConnectionInterface`  
Реализовать свой "мост" между существующим коннектом и этим Адаптером
```
    class MyDbConnection implements \Blade\Database\DbConnectionInterface
    {
        public function execute($sql, $bindings = []): int;
        public function each($sql, $bindings = [], callable $callback);
        public function beginTransaction();
        public function commit();
        public function rollBack();
    }
```


DbAdapter
---------
Класс представляет собой универсальную обертку над любым коннектом к БД.  
И реализует набор вспомогательных методов для выборки из базы.
```
    $db = new DbAdapter(new MyDbConnection);

        ->execute($query, array $bindings = []): bool
        ->selectList($query, array $bindings = []): array
        ->selectRow($query, array $bindings = []): array
        ->selectColumn($query, array $bindings = []): array
        ->selectValue($query, array $bindings = [])
        ->selectKeyValue($query, array $bindings = []): array

        ->chunk($pageSize, SqlBuilder $sql, callable $handler)

        ->beginTransaction();
        ->commit();
        ->rollBack();
        ->transaction(callable $func)

        ->getConnection(): \Blade\Database\DbConnectionInterface
```

SqlBuilder
----------
*настройка:*
```
    \Blade\Database\Sql\SqlBuilder::setEscapeMethod(function($value) {
        return php pg_escape_string($value);
    });
```
