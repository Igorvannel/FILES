<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Traitement des requêtes POST (AJAX)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tx_hash'])) {
    header('Content-Type: application/json');
    try {
        $user_id = $_SESSION['user_id'];
        $tx_hash = $_POST['tx_hash'];
        $currency = $_POST['currency'];
        $amount = $_POST['amount'];
        $plan_id = $_POST['plan_id'];
        
        // Créer la table si elle n'existe pas
        $sql = "CREATE TABLE IF NOT EXISTS transactions_crypto (
            id SERIAL PRIMARY KEY,
            user_id INTEGER REFERENCES users(id),
            tx_hash VARCHAR(100) UNIQUE,
            currency VARCHAR(10) NOT NULL,
            amount DECIMAL(15,8) NOT NULL,
            status VARCHAR(20) DEFAULT 'pending',
            type_invest VARCHAR(20) DEFAULT 'investment',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        pg_query($conn, $sql);
        
        // Insérer la transaction
        $result = pg_query_params($conn,
            "INSERT INTO transactions_crypto (user_id, tx_hash, currency, amount, status, type_invest) 
             VALUES ($1, $2, $3, $4, 'pending', 'investment')",
            array($user_id, $tx_hash, $currency, $amount)
        );

        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Transaction submitted successfully']);
        } else {
            throw new Exception(pg_last_error($conn));
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit();
}

// Récupération des informations utilisateur pour l'affichage
$user_id = $_SESSION['user_id'];
$result = pg_query_params($conn, "SELECT email, balance FROM users WHERE id = $1", array($user_id));
$user = pg_fetch_assoc($result);
?>
<!DOCTYPE html>

<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LAZADA'S INVESTMENT - Plans</title>
    <link rel="stylesheet" href="assets/css/vendor/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/all.min.css">
    <link rel="stylesheet" href="assets/css/line-awesome.min.css">
    <link rel="stylesheet" href="assets/css/vendor/animate.min.css">
    <link rel="stylesheet" href="assets/css/vendor/slick.css">
    <link rel="stylesheet" href="assets/css/vendor/dots.css">
    <link rel="stylesheet" href="assets/css/main.css">
    <style>
        body { background-color: #1a1a1a; color: white; }
        .modal-content {
            background-color: #1a1a1a;
            color: #fff;
            border: 1px solid #4d4d4d;
        }
        .modal-header {
            border-bottom: 1px solid #333;
        }
        .modal-header .close {
            color: #fff;
            opacity: 1;
        }
        .form-control {
            background-color: #2d2d2d;
            border: 1px solid #4d4d4d;
            color: #fff;
        }
        .form-control:focus {
            background-color: #333;
            border-color: #ffc107;
            color: #fff;
            box-shadow: none;
        }
        .address-text code {
            background-color: #2d2d2d;
            color: #ffc107;
            padding: 15px;
            border-radius: 5px;
            width: 100%;
            word-break: break-all;
        }
        .alert-info {
            background-color: #2d2d2d;
            border-color: #4d4d4d;
            color: #fff;
        }
        .currency-select label, .transaction-form label {
            color: #ffc107;
        }
        .btn-primary {
            background-color: #2d2d2d;
            border-color: #4d4d4d;
            color: #ffc107;
        }
        .btn-primary:hover {
            background-color: #333;
            border-color: #ffc107;
            color: #ffc107;
        }
        .cmn-btn {
            background-color: #ffc107;
            color: #000;
            border: none;
        }
        .cmn-btn:hover {
            background-color: #e0a800;
        }
        #planName {
            color: #ffc107;
        }
        .package-card {
            background-color: #1a1a1a;
            border: 1px solid #4d4d4d;
            padding: 20px;
            border-radius: 5px;
        }
        .section-title {
            color: white;
        }
        .section-title .base--color {
            color: #ffc107;
        }
        select.form-control {
            appearance: none;
            -webkit-appearance: none;
            background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23ffc107' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 1rem center;
            background-size: 1em;
        }
        .modal-loader {
            display: none;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 1000;
        }
        .modal-overlay {
            display: none;
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.5);
            z-index: 999;
        }
    </style>
</head>
<body>
    <!-- Le reste de votre HTML reste le même jusqu'au script -->

    <script>
    const cryptoAddresses = {
        'BTC': '1AqSt62gpYTLF3SaqiAqukMdFb5QiSfc7k',
        'USDT': '0x92f474c4d3152d6a4e400ada508d28d0d9ffd821'
    };

    let currentPlan = null;

    $('.investBtn').on('click', function() {
        currentPlan = {
            id: $(this).data('plan'),
            amount: $(this).data('amount').toString().split(' - ')[0], // Prend le montant minimum si c'est une plage
            name: $(this).data('name')
        };
        
        $('#planName').text(currentPlan.name);
        $('#cryptoAmount').text(currentPlan.amount);
        updateQRCode('BTC');
    });

    function showLoader() {
        $('.modal-loader').show();
        $('.modal-overlay').show();
    }

    function hideLoader() {
        $('.modal-loader').hide();
        $('.modal-overlay').hide();
    }

    function copyAddress() {
        const address = document.getElementById('walletAddress').textContent;
        navigator.clipboard.writeText(address)
            .then(() => alert('Address copied to clipboard!'))
            .catch(() => alert('Error copying address'));
    }

    function updateQRCode(currency) {
        const address = cryptoAddresses[currency];
        document.getElementById('walletAddress').textContent = address;
        document.getElementById('qrCode').src = `https://api.qrserver.com/v1/create-qr-code/?data=${encodeURIComponent(address)}&size=200x200`;
        document.querySelector('.currency-label').textContent = currency;
    }

    function submitTransaction() {
        const txHash = document.getElementById('txHash').value;
        if (!txHash.trim()) {
            alert('Please enter a transaction hash');
            return;
        }

        const currency = document.getElementById('cryptoCurrency').value;
        const submitBtn = document.querySelector('.transaction-form button');
        
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="las la-spinner la-spin"></i> Processing...';
        showLoader();
        
        $.ajax({
            url: window.location.href,
            method: 'POST',
            data: {
                tx_hash: txHash,
                currency: currency,
                amount: currentPlan.amount,
                plan_id: currentPlan.id
            },
            success: function(response) {
                if(response.success) {
                    alert(response.message);
                    window.location.href = 'dashboard.php';
                } else {
                    alert(response.message || 'Transaction failed');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error details:', xhr.responseText);
                alert('Error submitting transaction. Please try again.');
            },
            complete: function() {
                submitBtn.disabled = false;
                submitBtn.innerHTML = 'Confirm Payment';
                hideLoader();
            }
        });
    }

    $('#cryptoCurrency').change(function() {
        updateQRCode(this.value);
    });

    $('#cryptoModal').on('show.bs.modal', function() {
        const currency = $('#cryptoCurrency').val();
        updateQRCode(currency);
    });

    $('#cryptoModal').on('hidden.bs.modal', function() {
        $('#txHash').val('');
        currentPlan = null;
        hideLoader();
    });
    </script>
</body>
</html>