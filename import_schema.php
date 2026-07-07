<?php

$host = '127.0.0.1';
$db   = 'coffee_shop_system_db';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    
    // Read the SQL file
    $sql = file_get_contents('../docs/mvp1/pos_schema_mvp1_core.sql');
    
    // Add IF NOT EXISTS to prevent crashing on existing tables
    $sql = str_replace('CREATE TABLE ', 'CREATE TABLE IF NOT EXISTS ', $sql);
    
    // Execute the SQL
    $pdo->exec($sql);
    echo "Schema imported successfully with IF NOT EXISTS.\n";
    
} catch (\PDOException $e) {
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}
