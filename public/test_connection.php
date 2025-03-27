<?php

function loadEnv($filePath) {
    if (!file_exists($filePath)) {
        die("Erro: Arquivo .env não encontrado no caminho: " . $filePath);
    }

    $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue; // Ignora comentários

        list($key, $value) = explode('=', $line, 2);
        putenv(trim($key) . '=' . trim($value));
        $_ENV[trim($key)] = trim($value);
        $_SERVER[trim($key)] = trim($value);
    }
}

// Carregar o .env
loadEnv(__DIR__ . '/../.env');

try {
    // Conectar ao banco
    $dsn = 'pgsql:host=' . getenv('DB_HOST') . 
           ';port=' . getenv('DB_PORT') . 
           ';dbname=' . getenv('DB_NAME');

    $username = getenv('DB_USER');
    $password = getenv('DB_PASSWORD');

    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    echo " Conexão bem-sucedida!\n";

    // 🔍 Teste de consulta na tabela usuario
    echo "🔎 Buscando dados na tabela 'usuario'...\n";
    $stmt = $pdo->query("SELECT id, username, senha, perfil, status_bloqueio FROM public.usuario LIMIT 5");

    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($usuarios)) {
        echo "⚠️ Nenhum usuário encontrado na tabela.\n";
    } else {
        foreach ($usuarios as $usuario) {
            echo "---------------------------------\n";
            echo "ID: " . $usuario['id'] . "\n";
            echo "Username: " . $usuario['username'] . "\n";
            echo "Senha (hash): " . $usuario['senha'] . "\n";
            echo "Perfil: " . $usuario['perfil'] . "\n";
            echo "Status: " . $usuario['status_bloqueio'] . "\n";
        }
    }

} catch (PDOException $e) {
    die(" Erro na consulta: " . $e->getMessage());
}

