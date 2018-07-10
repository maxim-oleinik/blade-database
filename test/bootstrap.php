<?php

require_once __DIR__ . '/../vendor/autoload.php';

\Blade\Database\Sql\SqlBuilder::setEscapeMethod(function($value){
    return str_replace("'", "''", $value);
});
