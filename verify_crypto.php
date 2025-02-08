<?php
// verify_crypto.php - Script de vérification des transactions
function verifyBTCTransaction($tx_hash) {
    $url = "https://blockchain.info/rawtx/" . $tx_hash;
    $response = file_get_contents($url);
    $data = json_decode($response, true);
    
    if(isset($data['out'])) {
        foreach($data['out'] as $output) {
            if($output['addr'] === 'VOTRE_ADRESSE_BTC') {
                return [
                    'verified' => true,
                    'amount' => $output['value'] / 100000000, // Convertir satoshis en BTC
                    'confirmations' => $data['confirmations'] ?? 0
                ];
            }
        }
    }
    return ['verified' => false];
}

function verifyUSDTTransaction($tx_hash) {
    // Utilisez l'API Tether ou Etherscan pour USDT
    $url = "https://api.etherscan.io/api?module=proxy&action=eth_getTransactionByHash&txhash=" . $tx_hash . "&apikey=VOTRE_CLE_API";
    $response = file_get_contents($url);
    $data = json_decode($response, true);
    
    // Vérification USDT
    if(isset($data['result'])) {
        if($data['result']['to'] === 'VOTRE_ADRESSE_USDT') {
            return [
                'verified' => true,
                'amount' => hexdec($data['result']['value']) / 1e6, // Convertir en USDT
                'confirmations' => $data['result']['confirmations'] ?? 0
            ];
        }
    }
    return ['verified' => false];
}

// Vérification périodique des transactions en attente
$query = "SELECT * FROM transactions_crypto WHERE status = 'pending'";
$result = pg_query($conn, $query);

while($transaction = pg_fetch_assoc($result)) {
    $verification = $transaction['currency'] === 'BTC' 
        ? verifyBTCTransaction($transaction['tx_hash'])
        : verifyUSDTTransaction($transaction['tx_hash']);
    
    if($verification['verified'] && $verification['confirmations'] >= 3) {
        // Mettre à jour le statut de la transaction
        pg_query_params($conn,
            "UPDATE transactions_crypto SET status = 'completed' WHERE id = $1",
            array($transaction['id'])
        );
        
        // Mettre à jour le solde de l'utilisateur
        pg_query_params($conn,
            "UPDATE users SET balance = balance + $1 WHERE id = $2",
            array($verification['amount'], $transaction['user_id'])
        );
        
        // Envoyer une notification à l'utilisateur
        // TODO: Implémenter le système de notifications
    }
}