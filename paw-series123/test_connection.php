<?php
require __DIR__ . '/db_connect.php';

try {
    getDatabaseConnection();
    echo 'Connection successful';
} catch (Throwable $e) {
    echo 'Connection failed';
}

