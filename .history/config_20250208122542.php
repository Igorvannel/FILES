<?php

header('Content-Type: text/html; charset=utf-8');
mb_internal_encoding('UTF-8');
// config.php
define('DB_HOST', '192.99.42.107');
define('DB_PORT', '5432');
define('DB_NAME', 'invest_db');
define('DB_USER', 'admin');
define('DB_PASS', 'admin');

try {
    $conn = pg_connect("host=" . DB_HOST . " port=" . DB_PORT . " dbname=" . DB_NAME . " user=" . DB_USER . " password=" . DB_PASS);
    
    if (!$conn) {
        throw new Exception("Erreur de connexion à PostgreSQL");
    }
    
    // Créer la table users si elle n'existe pas
    $sql = "CREATE TABLE IF NOT EXISTS users (
        id SERIAL PRIMARY KEY,
        fullname VARCHAR(100) NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        balance DECIMAL(15,2) DEFAULT 0.00,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";

$sql = "CREATE TABLE IF NOT EXISTS transactions (
    id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES users(id),
    amount DECIMAL(15,2) NOT NULL,
    type VARCHAR(20) NOT NULL CHECK (type IN ('investment', 'earning')),
    status VARCHAR(20) NOT NULL DEFAULT 'pending' CHECK (status IN ('pending', 'completed', 'failed')),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

    pg_query($conn, $sql);
    
} catch(Exception $e) {
    die("Erreur : " . $e->getMessage());
}