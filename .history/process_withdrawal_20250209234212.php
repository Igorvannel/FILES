<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

header('Content-Type: application/json');

try {
    $user_id = $_SESSION['user_id'];
    $method = $_POST['method'];
    $amount = floatval($_POST['amount']);
    $wallet_address = $_POST['wallet_address'];

    // Validation
    if ($amount < 50) {
        throw new Exception('Minimum withdrawal amount is $50');
    }

    if (empty($wallet_address)) {
        throw new Exception('Wallet address is required');
    }

    // Vérifier le solde
    $balance_query = pg_query_params($conn, 
        "SELECT balance FROM users WHERE id = $1", 
        array($user_id)
    );
    $user = pg_fetch_assoc($balance_query);

    if ($user['balance'] < $amount) {
        throw new Exception('Insufficient balance');
    }

    // Début de la transaction
    pg_query($conn, "BEGIN");

    // Créer la demande de retrait
    $result = pg_query_params($conn,
        "INSERT INTO transactions_crypto (user_id, wallet_address, currency, amount, status, type_invest) 
         VALUES ($1, $2, $3, $4, 'pending', 'withdrawal')",
        array($user_id, $wallet_address, $method, $amount)
    );

    if (!$result) {
        throw new Exception(pg_last_error($conn));
    }

    // Mettre à jour le solde
    $update = pg_query_params($conn,
        "UPDATE users SET balance = balance - $1 WHERE id = $2",
        array($amount, $user_id)
    );

    if (!$update) {
        throw new Exception('Failed to update balance');
    }

    // Si tout est ok, on commit
    pg_query($conn, "COMMIT");
    
    echo json_encode(['success' => true, 'message' => 'Withdrawal request submitted successfully']);

} catch (Exception $e) {
    pg_query($conn, "ROLLBACK");
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>