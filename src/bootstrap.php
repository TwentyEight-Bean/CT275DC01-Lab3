<?php
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/../libraries/Psr4AutoloaderClass.php';

$loader = new Psr4AutoloaderClass();
$loader->register();
$loader->addNamespace('CT275\Labs', __DIR__ . '/classes');

try {
    $PDO = (new CT275\Labs\PDOFactory())->create([
        'dbhost' => 'localhost',
        'dbname' => 'ct275_lab3',
        'dbuser' => 'postgres',
        'dbpass' => '321'
    ]);
} catch (\Exception $ex) {
    echo 'Không thể kết nối đến PostgreSQL, kiểm tra lại cấu hình.<br>';
    exit("<pre>{$ex}</pre>");
}