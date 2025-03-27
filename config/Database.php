<?php

require_once __DIR__ . '/libs/phpdotenv/src/Dotenv.php';

// Inicializar e carregar o .env
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Definir variáveis do banco de dados
$host = $_ENV['DB_HOST'];
$dbname = $_ENV['DB_DATABASE'];
$username = $_ENV['DB_USERNAME'];
$password = $_ENV['DB_PASSWORD'];

try {
    $pdo = new \PDO(
        "pgsql:host=$host;dbname=$dbname",
        $username,
        $password
    );
    $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
} catch (\PDOException $e) {
    die("Erro de conexão: " . $e->getMessage());
}
