Blade Database Adapter
======================

Работа с БД

Install
-------
1. Add to composer.json

2. Implement \Blade\Database\DbConnectionInterface
```
    class MyDbConnection implements \Blade\Database\DbConnectionInterface
```


DbAdapter
---------
```
    $db = new DbAdapter(new MyDbConnection);

        ->getConnection(): \Blade\Database\DbConnectionInterface
        ->execute($query, array $bindings = []): bool
        ->selectList($query, array $bindings = []): array
        ->selectRow($query, array $bindings = []): array
        ->selectColumn($query, array $bindings = []): array
        ->selectValue($query, array $bindings = [])
        ->selectKeyValue($query, array $bindings = []): array
        ->transaction(callable $func)
        ->chunk($pageSize, SqlBuilder $sql, callable $handler)
```

SqlBuilder
----------
*настройка:*
```
    \Blade\Database\Sql\SqlBuilder::setEscapeMethod(function($value) {
        return php pg_escape_string($value);
    });
```
