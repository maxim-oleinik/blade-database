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
        ->execute($sql, $bindings = []): int;
        ->each($sql, $bindings = [], callable $callback);
        ->beginTransaction();
        ->commit();
        ->rollBack();
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
####Настройка
```
    \Blade\Database\Sql\SqlBuilder::setEscapeMethod(function($value) {
        return pg_escape_string($value);
    });
```

####Select
```
    $sql = SqlBuilder::make('comment label')
        ->select("id, code, sum(code) as codes")
        ->addSelect("name")
        ->from("my_table", $alias = 't')
        ->setFromAlias('t')
.
        ->addJoin("LEFT JOIN authors AS a ON (a.id=t.author_id)")
.
        ->andWhere("t.id = %d", 123)
        ->andWhere("a.name > '%s'", 'some text')
        ->andWhereIn("t.code", [1,2,3])
        ->andWhereNotIn("t.code", [4,5])
.
        ->orderBy("id ASC, code")
        ->addOrder("name DESC")
.
        ->groupBy("t.code")
        ->having("sum(code) > 10")
.
        ->limit(10, $offset = 20);
    echo $sql;
```
**Подстановка count()**
```
    SqlBuilder::make()
        ->from("my_table")
        ->count($fields = '*');
```
**exists() = SELECT 1**
```
    SqlBuilder::make('comment label')
        ->from("my_table")
        ->exists(); // 1, если записи существуют
```

####Insert
```
    SqlBuilder::make()
        ->insert("my_table")
        ->values([
            'code' => 5,
            'name' => 'some text',
        ])
        ->returning('id, code');
```
**Вставка нескольких строк**
```
    SqlBuilder::make()
        ->insert("my_table")
        ->batchMode()
        ->values([
            [
                'code' => 5,
                'name' => 'some text',
            ],
            [
                'code' => 6,
                'name' => 'some text',
            ],
        ]);
```

####Update
```
    SqlBuilder::make()
        ->update("my_table")
        ->andWhere("id = %d", 123)
        ->values([
            'code' => 5,
            'name' => 'some text',
        ]);
```

####Delete
```
    SqlBuilder::make()
        ->delete("my_table")
        ->andWhere("id = %d", 123)
```
