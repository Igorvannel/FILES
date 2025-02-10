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
    
    // Vérifier le montant des gains
    $result = pg_query_params($conn, 
        "SELECT referral_earnings FROM users WHERE id = $1", 
        array($user_id)
    );
    $user = pg_fetch_assoc($result);
    
    if ($user['referral_earnings'] < 50) {
        throw new Exception('Minimum amount of $50 not reached');
    }

    // Début de la transaction
    pg_query($conn, "BEGIN");
    
    // Transférer les gains vers la balance
    $update = pg_query_params($conn,
        "UPDATE users SET 
         balance = balance + referral_earnings,
         referral_earnings = 0
         WHERE id = $1
         RETURNING referral_earnings AS transferred_amount",
        array($user_id)
    );

    if (!$update) {
        throw new Exception(pg_last_error($conn));
    }

    $transferred = pg_fetch_assoc($update);

    // Enregistrer la transaction
    $result = pg_query_params($conn,
        "INSERT INTO transactions_crypto (
            user_id, 
            amount, 
            currency, 
            status,
            type_invest,
            tx_hash
        ) VALUES ($1, $2, 'USD', 'completed', 'referral_transfer', $3)",
        array(
            $user_id, 
            $transferred['transferred_amount'],
            'REF_TRANSFER_'.time().'_'.$user_id
        )
    );

    if (!$result) {
        throw new Exception(pg_last_error($conn));
    }

    // Commit la transaction
    pg_query($conn, "COMMIT");
    
    echo json_encode([
        'success' => true,
        'success' => true,
        'message' => 'Successfully transferred $' . number_format($transferred['transferred_amount'], 2) . ' to your balance'
    ]);

} catch (Exception $e) {
    // En cas d'erreur, annuler toutes les modifications
    pg_query($conn, "ROLLBACK");
    
    echo json_encode([
        'success' => false, 
        'message' => $e->getMessage()
    ]);
}
?>