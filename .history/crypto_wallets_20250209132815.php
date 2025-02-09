<?php
// crypto_wallets.php - Gérer les adresses de cryptomonnaies

// Inclure la configuration de la base de données
include('config.php');

// Obtenez la connexion à la base de données
$conn = getDbConnection();

// Créer la table des portefeuilles crypto si elle n'existe pas
$sql = "CREATE TABLE IF NOT EXISTS crypto_wallets (
    id SERIAL PRIMARY KEY,
    currency VARCHAR(10) NOT NULL,
    address VARCHAR(100) NOT NULL,
    qr_code VARCHAR(255),
    is_active BOOLEAN DEFAULT true
)";
pg_query($conn, $sql);


pg_query($conn, $sql);
?>
