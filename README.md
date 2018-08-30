Blade Database Adapter
======================

Универсальный адаптер к любому уже реализованному коннекту к БД.

Install
-------
1. Add to `composer`
    ```
        composer require blade/database
    ```

2. Implement `\Blade\Database\DbConnectionInterface`  
Реализовать "мост" между своим коннектом к базе и этим Адаптером
    ```
        class MyDbConnection implements \Blade\Database\DbConnectionInterface
        {
            ->execute($sql, $bindings = []): int;
            ->each($sql, callable $callback, $bindings = []);
            ->escape($value): string
            ->beginTransaction();
            ->commit();
            ->rollBack();
        }
    ```
    Или использовать готовый:
    ```
        // PDO
        $dsn = sprintf('mysql:host=%s;port=%d;dbname=%s', $host, $port, $dbName);
        $connection = new \Blade\Database\Connection\PdoConnection($dsn, $user, $pass, [
            \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
        ]);
    ```
    ```
        // PostgreSQL (pgsql)
        $dsn = sprintf('host=%s port=%d dbname=%s user=%s password=%s',
            $host, $port, $dbName, $user, $pass
        );
        $connection = new \Blade\Database\Connection\PostgresConnection($dsn, PGSQL_CONNECT_FORCE_NEW);
    ```
    ```
        // MySQL (mysqli)
        $connection = new \Blade\Database\Connection\MysqlConnection(
            $host, $user, $pass, $dbName, $port
        );
    ```


DbAdapter
---------
Класс представляет собой универсальную обертку над любым коннектом к БД.  
И реализует набор вспомогательных методов для выборки из базы.
```
    $db = new DbAdapter(new MyDbConnection);

        ->execute($query, array $bindings = []): int          - Кол-во затронутых строк
        ->selectAll($query, array $bindings = []): array      - Выбрать всю выборку в один массив
        ->selectRow($query, array $bindings = []): array      - Выбрать одну строку
        ->selectColumn($query, array $bindings = []): array   - Выбрать значение 1 колонки в массив
        ->selectValue($query, array $bindings = []): string   - Выбрать единственное значение
        ->selectKeyValue($query, array $bindings = []): array - Выбрать значения 2ух колонок в ассоциативный массив col1 => $col2

        ->chunk($pageSize, SqlBuilder $sql, callable $handler) - Разбить выборку на части и вызвать $handler(array $rows) для каждой

        ->beginTransaction();
        ->commit();
        ->rollBack();
        ->transaction(callable $func)

        ->getConnection(): \Blade\Database\DbConnectionInterface
```

SqlBuilder
----------
### Настройка
```
    \Blade\Database\Sql\SqlBuilder::setEscapeMethod(function($value) {
        return pg_escape_string($value);
    });

    // или
    \Blade\Database\Sql\SqlBuilder::setEscapeMethod([$connection, 'escape']);
```

### Select
```
    $sql = SqlBuilder::make('comment label')
        ->select("id, code, sum(code) as codes")
        ->addSelect("name")
        ->from("my_table", $alias = 't')
        ->setFromAlias('t')

        ->addJoin("LEFT JOIN authors AS a ON (a.id=t.author_id)")

        ->andWhere("t.id = %d", 123)
        ->andWhere("a.name > '%s'", 'some text')
        ->andWhereIn("t.code", [1,2,3])
        ->andWhereNotIn("t.code", [4,5])

        ->orderBy("id ASC, code")
        ->addOrder("name DESC")

        ->groupBy("t.code")
        ->having("sum(code) > 10")

        ->limit(10, $offset = 20);
    echo $sql;
```

**Join**
```
    $sql = SqlBuilder::make()
        ->addJoin("LEFT JOIN authors AS a ON (a.id=t.author_id)")
        ->andWhere("a.name > '%s'", 'some text');

    // Объединение 2ух запросов в Join
    $sqlAuthors = SqlBuilder::make()->from('authors', 'a')
        ->andWhere("a.name > '%s'", 'some text');

    $sql = SqlBuilder::make()->from("my_table", 't')
        ->innerJoin($sqlAuthors, 'ON (a.id=t.author_id)')
        ->leftJoin($sqlAuthors, 'USING author_id')
        ->rightJoin($sqlAuthors)
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

### Insert
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

### Update
```
    SqlBuilder::make()
        ->update("my_table")
        ->andWhere("id = %d", 123)
        ->values([
            'code' => 5,
            'name' => 'some text',
        ]);
```

### Delete
```
    SqlBuilder::make()
        ->delete("my_table")
        ->andWhere("id = %d", 123)
```
