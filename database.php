<?php
require __DIR__ . '/edgedb/src/Client.php';
require __DIR__ . '/edgedb/src/Query.php';
require __DIR__ . '/edgedb/src/Exception.php';

use EdgeDB\Client;

$client = new Client([
    'host' => getenv('EDGEDB_HOST'),
    'port' => getenv('EDGEDB_PORT'),
    'user' => getenv('EDGEDB_USER'),
    'password' => getenv('EDGEDB_PASSWORD'),
    'database' => getenv('EDGEDB_DATABASE')
]);
?>
