<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$result = pg_query_params($conn, "SELECT email, balance FROM users WHERE id = $1", array($user_id));
$user = pg_fetch_assoc($result);

// Récupérer les méthodes de paiement actives
$methods = pg_query($conn, "SELECT * FROM payment_methods WHERE is_active = true");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <!-- ... Head content ... -->
    <title>Deposit - Lazada Investment</title>
</head>
<body>
    <!-- ... Header ... -->
    
    <div class="pt-120 pb-120">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">Deposit Funds</h4>
                        </div>
                        <div class="card-body">
                            <form id="depositForm">
                                <div class="form-group">
                                    <label>Select Payment Method</label>
                                    <select class="form-control" id="paymentMethod">
                                        <?php while ($method = pg_fetch_assoc($methods)): ?>
                                        <option value="<?php echo $method['code']; ?>" 
                                                data-address="<?php echo $method['address']; ?>"
                                                data-min="<?php echo $method['min_deposit']; ?>">
                                            <?php echo $method['name']; ?>
                                        </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label>Amount (USD)</label>
                                    <input type="number" class="form-control" id="amount" min="10" step="1" required>
                                    <small class="text-muted">Minimum deposit: $<span id="minDeposit">10</span></small>
                                </div>

                                <div class="wallet-info mt-4">
                                    <h5>Send payment to:</h5>
                                    <div class="qr-code text-center my-3">
                                        <img id="qrCode" src="" alt="QR Code">
                                    </div>
                                    <div class="address-box">
                                        <code id="walletAddress" class="d-block p-3 bg-dark"></code>
                                        <button type="button" class="btn btn-sm btn-primary mt-2" onclick="copyAddress()">
                                            Copy Address
                                        </button>
                                    </div>
                                </div>

                                <div class="form-group mt-4">
                                    <label>Transaction Hash</label>
                                    <input type="text" class="form-control" id="txHash" required>
                                </div>

                                <button type="submit" class="cmn-btn w-100 mt-4">
                                    Submit Deposit
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    $(document).ready(function() {
        updatePaymentMethod();
        
        $('#paymentMethod').change(function() {
            updatePaymentMethod();
        });

        $('#depositForm').submit(function(e) {
            e.preventDefault();
            submitDeposit();
        });
    });

    function updatePaymentMethod() {
        const selected = $('#paymentMethod option:selected');
        const address = selected.data('address');
        const minDeposit = selected.data('min');
        
        $('#walletAddress').text(address);
        $('#minDeposit').text(minDeposit);
        $('#amount').attr('min', minDeposit);
        
        updateQRCode(address);
    }

    function updateQRCode(address) {
        $('#qrCode').attr('src', 
            `https://api.qrserver.com/v1/create-qr-code/?data=${address}&size=200x200`
        );
    }

    function copyAddress() {
        const address = $('#walletAddress').text();
        navigator.clipboard.writeText(address)
            .then(() => alert('Address copied!'));
    }

    function submitDeposit() {
        const data = {
            method: $('#paymentMethod').val(),
            amount: $('#amount').val(),
            tx_hash: $('#txHash').val()
        };

        $.ajax({
            url: 'process_deposit.php',
            method: 'POST',
            data: data,
            success: function(response) {
                if (response.success) {
                    alert('Deposit submitted successfully!');
                    window.location.href = 'dashboard.php';
                } else {
                    alert(response.message);
                }
            }
        });
    }
    </script>
</body>
</html>