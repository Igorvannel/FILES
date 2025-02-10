<?php
session_start();
require_once 'config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

try {
    $user_id = $_SESSION['user_id'];
    $amount = floatval($_POST['amount']);
    $method = $_POST['method'];
    $wallet = $_POST['wallet_address'];

    // Vérifier le solde
    $result = pg_query_params($conn, 
        "SELECT balance FROM users WHERE id = $1", 
        array($user_id)
    );
    $user = pg_fetch_assoc($result);

    if ($user['balance'] < $amount) {
        throw new Exception('Insufficient balance');
    }

    // Début de la transaction
    pg_query($conn, "BEGIN");

    // Créer la demande de retrait
    $result = pg_query_params($conn,
        "INSERT INTO withdrawals (user_id, amount, method, wallet_address, status) 
         VALUES ($1, $2, $3, $4, 'pending')",
        array($user_id, $amount, $method, $wallet)
    );

    // Réduire le solde
    $result2 = pg_query_params($conn,
        "UPDATE users SET balance = balance - $1 WHERE id = $2",
        array($amount, $user_id)
    );

    if ($result && $result2) {
        pg_query($conn, "COMMIT");
        echo json_encode(['success' => true]);
    } else {
        throw new Exception('Error processing withdrawal');
    }

} catch (Exception $e) {
    pg_query($conn, "ROLLBACK");
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}