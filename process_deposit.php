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
    $tx_hash = $_POST['tx_hash'];

    // Validation
    if ($amount < 10) {
        throw new Exception('Minimum deposit amount is $10');
    }

    if (empty($tx_hash)) {
        throw new Exception('Transaction hash is required');
    }

    // Début de la transaction
    pg_query($conn, "BEGIN");

    // Vérifier si le hash existe déjà
    $check = pg_query_params($conn, 
        "SELECT id FROM transactions_crypto WHERE tx_hash = $1", 
        array($tx_hash)
    );

    if (pg_num_rows($check) > 0) {
        throw new Exception('This transaction has already been submitted');
    }

    // Insérer la transaction
    $result = pg_query_params($conn,
        "INSERT INTO transactions_crypto (user_id, tx_hash, currency, amount, status, type_invest) 
         VALUES ($1, $2, $3, $4, 'pending', 'deposit')",
        array($user_id, $tx_hash, $method, $amount)
    );

    if (!$result) {
        throw new Exception(pg_last_error($conn));
    }

    // Mettre à jour le solde de l'utilisateur
    $update_balance = pg_query_params($conn,
        "UPDATE users SET balance = balance + $1 WHERE id = $2",
        array($amount, $user_id)
    );

    if (!$update_balance) {
        throw new Exception('Failed to update balance');
    }

    // Si tout est ok, on commit
    pg_query($conn, "COMMIT");
    
    echo json_encode(['success' => true, 'message' => 'Deposit submitted successfully']);

} catch (Exception $e) {
    pg_query($conn, "ROLLBACK");
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>