<?php
function getDatabaseConnection(): PDO
{
    $configPath = __DIR__ . '/config.php';
    if (!file_exists($configPath)) {
        throw new RuntimeException('Fichier config.php introuvable.');
    }

    $config = require $configPath;
    $dsn = sprintf('mysql:host=%s;dbname=%s;charset=utf8mb4', $config['host'], $config['database']);

    try {
        $pdo = new PDO($dsn, $config['username'], $config['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        return $pdo;
    } catch (PDOException $exception) {
        $message = sprintf('[%s] Connexion échouée : %s', date('Y-m-d H:i:s'), $exception->getMessage());
        error_log($message . PHP_EOL, 3, __DIR__ . '/db_errors.log');
        throw new RuntimeException('Impossible de se connecter à la base de données.');
    }
}

