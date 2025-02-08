<?php
// process_crypto.php - Traiter les paiements crypto

// Inclure la configuration de la base de données
include('config.php');

// Obtenez la connexion à la base de données
$conn = getDbConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if(isset($data['tx_hash'])) {
        $tx_hash = $data['tx_hash'];
        $currency = $data['currency'];
        $amount = $data['amount'];
        
        // Insérer la transaction dans la table
        $query = "INSERT INTO transactions_crypto (user_id, tx_hash, currency, amount, status) 
                 VALUES ($1, $2, $3, $4, 'pending')";
        $result = pg_query_params($conn, $query, array(
            $_SESSION['user_id'],  // Assurez-vous que l'ID utilisateur est défini dans la session
            $tx_hash,
            $currency,
            $amount
        ));
        
        if($result) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Transaction failed']);
        }
    }
}
?>
