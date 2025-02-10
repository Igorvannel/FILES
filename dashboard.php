<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$query = "SELECT * FROM users WHERE id = $1";
$result = pg_query_params($conn, $query, array($user_id));
$user = pg_fetch_assoc($result);

$query = "SELECT * FROM transactions_crypto WHERE user_id = $1 ORDER BY created_at DESC";
$transactions = pg_query_params($conn, $query, array($user_id));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LAZADA'S INVESTMENT - Dashboard</title>
    <link rel="icon" type="image/png" href="assets/images/favicon.png" sizes="16x16">
    <link rel="stylesheet" href="assets/css/vendor/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/all.min.css">
    <link rel="stylesheet" href="assets/css/line-awesome.min.css">
    <link rel="stylesheet" href="assets/css/vendor/animate.min.css">
    <link rel="stylesheet" href="assets/css/vendor/slick.css">
    <link rel="stylesheet" href="assets/css/vendor/dots.css">
    <link rel="stylesheet" href="assets/css/main.css">
    <style>
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
        }
        .wallet-address {
            background: #2d2d2d;
            padding: 15px;
            border-radius: 5px;
            word-break: break-all;
            font-family: monospace;
            margin: 10px 0;
        }
        .deposit-withdraw-buttons {
            margin: 20px 0;
            text-align: center;
        }
        .deposit-withdraw-buttons .cmn-btn {
            margin: 0 10px;
        }
    </style>
</head>
<body>
    <div class="preloader">
        <div class="preloader-container">
            <span class="animated-preloader"></span>
        </div>
    </div>

    <header class="header">
        <div class="header__bottom">
            <div class="container">
                <nav class="navbar navbar-expand-xl p-0 align-items-center">
                    <a class="site-logo site-title" href="dashboard.php">
                        <img src="https://cdn.freelogovectors.net/wp-content/uploads/2023/10/lazada-logo-freelogovectors.net_-640x400.png" alt="site-logo">
                    </a>
                    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent">
                        <span class="menu-toggle"></span>
                    </button>
                    <div class="collapse navbar-collapse" id="navbarSupportedContent">
                        <ul class="navbar-nav main-menu m-auto">
                            <li><a href="dashboard.php">Dashboard</a></li>
                            <li><a href="plan.php">Invest</a></li>
                            <li><a href="transactions.php">Transactions</a></li>
                            <li><a href="referral.php">Referrals</a></li>
                            <li><a href="logout.php">Logout</a></li>
                        </ul>
                        <div class="nav-right">
                            <ul class="account-menu ml-3">
                                <li>
                                    <span class="d-inline-block text-white">
                                        <i class="las la-user-circle"></i> 
                                        <?php echo htmlspecialchars($user['email']); ?>
                                    </span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </nav>
            </div>
        </div>
    </header>

    <section class="inner-hero bg_img" data-background="assets/images/bg/bg-1.jpg">
        <div class="container">
            <div class="row">
                <div class="col-lg-6">
                    <h2 class="page-title">Dashboard</h2>
                    <ul class="page-breadcrumb">
                        <li><a href="dashboard.php">Home</a></li>
                        <li>Dashboard</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <div class="pt-120 pb-120">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-12">
                    <div class="row mb-none-30">
                        <div class="col-xl-4 col-sm-6 mb-30">
                            <div class="d-widget d-flex flex-wrap">
                                <div class="col-8">
                                    <span class="caption">Balance</span>
                                    <h4 class="currency-amount">$<?php echo number_format($user['balance'], 2); ?></h4>
                                </div>
                                <div class="col-4">
                                    <div class="icon ml-auto">
                                        <i class="las la-dollar-sign"></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-4 col-sm-6 mb-30">
                            <div class="d-widget d-flex flex-wrap">
                                <div class="col-8">
                                    <span class="caption">Total Investments</span>
                                    <h4 class="currency-amount">
                                        <?php
                                        $query = "SELECT COALESCE(SUM(amount), 0) as total FROM transactions_crypto WHERE user_id = $1 AND type_invest = 'investment'";
                                        $result = pg_query_params($conn, $query, array($user_id));
                                        $total = pg_fetch_assoc($result);
                                        echo '$' . number_format($total['total'], 2);
                                        ?>
                                    </h4>
                                </div>
                                <div class="col-4">
                                    <div class="icon ml-auto">
                                        <i class="las la-cubes"></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-4 col-sm-6 mb-30">
                            <div class="d-widget d-flex flex-wrap">
                                <div class="col-8">
                                    <span class="caption">Total Earnings</span>
                                    <h4 class="currency-amount">
                                        <?php
                                        $query = "SELECT COALESCE(SUM(amount), 0) as total FROM transactions_crypto WHERE user_id = $1 AND type_invest = 'earning'";
                                        $result = pg_query_params($conn, $query, array($user_id));
                                        $total = pg_fetch_assoc($result);
                                        echo '$' . number_format($total['total'], 2);
                                        ?>
                                    </h4>
                                </div>
                                <div class="col-4">
                                    <div class="icon ml-auto">
                                        <i class="las la-wallet"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Deposit/Withdraw Buttons -->
                    <div class="deposit-withdraw-buttons">
                        <button type="button" class="cmn-btn" data-toggle="modal" data-target="#depositModal">
                            <i class="las la-plus"></i> Deposit
                        </button>
                        <button type="button" class="cmn-btn" data-toggle="modal" data-target="#withdrawModal">
                            <i class="las la-money-bill-wave"></i> Withdraw
                        </button>
                    </div>

                    <div class="row mt-50">
                        <div class="col-lg-12">
                            <h3>Recent Transactions</h3>
                            <div class="table-responsive--md p-0">
                                <table class="table style--two white-space-nowrap">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Amount</th>
                                            <th>Type</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($transaction = pg_fetch_assoc($transactions)): ?>
                                        <tr>
                                            <td data-label="Date"><?php echo date('d/m/Y', strtotime($transaction['created_at'])); ?></td>
                                            <td data-label="Amount">
                                                <span class="<?php echo $transaction['type_invest'] == 'earning' ? 'text-success' : 'text-info'; ?>">
                                                    <?php echo ($transaction['type_invest'] == 'earning' ? '+' : '') . '$' . number_format($transaction['amount'], 2); ?>
                                                </span>
                                            </td>
                                            <td data-label="Type">
                                                <span class="badge base--bg"><?php echo ucfirst($transaction['type_invest']); ?></span>
                                            </td>
                                            <td data-label="Status">
                                                <span class="badge <?php echo $transaction['status'] == 'completed' ? 'badge-success' : 'badge-warning'; ?>">
                                                    <?php echo ucfirst($transaction['status']); ?>
                                                </span>
                                            </td>
                                        </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modals -->
    <!-- Deposit Modal -->
    <div class="modal fade" id="depositModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Deposit Funds</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="depositForm">
                        <div class="form-group">
                            <label>Select Payment Method</label>
                            <select class="form-control" id="paymentMethod">
                                <option value="BTC">Bitcoin (BTC)</option>
                                <option value="USDT">Binance</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Amount USD</label>
                            <input type="number" class="form-control" id="depositAmount" min="10" required>
                            <small class="text-muted">Minimum deposit: $10</small>
                        </div>

                        <div class="wallet-info mt-4">
                            <h6>Send payment to:</h6>
                            <div class="qr-code text-center my-3">
                                <img id="qrCode" src="" alt="QR Code" class="img-fluid">
                            </div>
                            <div class="wallet-address">
                                <code id="walletAddress"></code>
                            </div>
                            <button type="button" class="btn btn-primary btn-sm" onclick="copyAddress()">
                                <i class="las la-copy"></i> Copy Address
                            </button>
                        </div>

                        <div class="form-group mt-4">
                            <label>Transaction Hash</label>
                            <input type="text" class="form-control" id="txHash" required>
                        </div>

                        <button type="submit" class="cmn-btn w-100 mt-3">Confirm Deposit</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Withdraw Modal -->
    <div class="modal fade" id="withdrawModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Withdraw Funds</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="withdrawForm">
                        <div class="form-group">
                            <label>Select Payment Method</label>
                            <select class="form-control" name="withdrawMethod">
                                <option value="BTC">Bitcoin (BTC)</option>
                                <option value="USDT">Binance</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Amount USD</label>
                            <input type="number" class="form-control" name="withdrawAmount" min="50" max="<?php echo $user['balance']; ?>" required>
                            <small class="text-muted">Minimum withdrawal: $50</small>
                            <small class="d-block text-muted">Available balance: $<?php echo number_format($user['balance'], 2); ?></small>
                        </div>

                        <div class="form-group">
                            <label>Your Wallet Address</label>
                            <input type="text" class="form-control" name="walletAddress" required>
                        </div>

                        <button type="submit" class="cmn-btn w-100 mt-3">Request Withdrawal</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer bg_img">
        <div class="footer__bottom">
            <div class="container">
                <div class="row">
                    <div class="col-md-6 text-md-left text-center">
                        <p>© 2024 <a href="index.html" class="base--color">LAZADA</a>. All rights reserved</p>
                        </div>
                    <div class="col-md-6">
                        <ul class="social-link-list d-flex flex-wrap justify-content-md-end justify-content-center">
                            <li><a href="#0" data-toggle="tooltip" data-placement="top" title="Facebook"><i class="lab la-facebook-f"></i></a></li>
                            <li><a href="#0" data-toggle="tooltip" data-placement="top" title="Twitter"><i class="lab la-twitter"></i></a></li>
                            <li><a href="#0" data-toggle="tooltip" data-placement="top" title="Pinterest"><i class="lab la-pinterest-p"></i></a></li>
                            <li><a href="#0" data-toggle="tooltip" data-placement="top" title="LinkedIn"><i class="lab la-linkedin-in"></i></a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <script src="assets/js/vendor/jquery-3.5.1.min.js"></script>
    <script src="assets/js/vendor/bootstrap.bundle.min.js"></script>
    <script src="assets/js/vendor/slick.min.js"></script>
    <script src="assets/js/vendor/wow.min.js"></script>
    <script src="assets/js/app.js"></script>

    <script>
    const cryptoAddresses = {
        'BTC': '1AqSt62gpYTLF3SaqiAqukMdFb5QiSfc7k',
        'USDT': '0x92f474c4d3152d6a4e400ada508d28d0d9ffd821'
    };

    $(document).ready(function() {
        // Initialisation du QR code
        updateQRCode('BTC');

        $('#paymentMethod').change(function() {
            updateQRCode($(this).val());
        });

        // Gestion du formulaire de dépôt
        $('#depositForm').submit(function(e) {
            e.preventDefault();
            const submitBtn = $(this).find('button[type="submit"]');
            submitBtn.prop('disabled', true).html('<i class="las la-spinner la-spin"></i> Processing...');

            $.ajax({
                url: 'process_deposit.php',
                method: 'POST',
                data: {
                    method: $('#paymentMethod').val(),
                    amount: $('#depositAmount').val(),
                    tx_hash: $('#txHash').val()
                },
                success: function(response) {
                    if(response.success) {
                        alert('Deposit submitted successfully! Please wait for confirmation.');
                        location.reload();
                    } else {
                        alert(response.message || 'Transaction failed');
                    }
                },
                error: function() {
                    alert('Error processing deposit');
                },
                complete: function() {
                    submitBtn.prop('disabled', false).text('Confirm Deposit');
                }
            });
        });

        // Gestion du formulaire de retrait
        $('#withdrawForm').submit(function(e) {
            e.preventDefault();
            const submitBtn = $(this).find('button[type="submit"]');
            submitBtn.prop('disabled', true).html('<i class="las la-spinner la-spin"></i> Processing...');

            $.ajax({
                url: 'process_withdrawal.php',
                method: 'POST',
                data: {
                    method: $('select[name="withdrawMethod"]').val(),
                    amount: $('input[name="withdrawAmount"]').val(),
                    wallet_address: $('input[name="walletAddress"]').val()
                },
                success: function(response) {
                    if(response.success) {
                        alert('Withdrawal request submitted successfully!');
                        location.reload();
                    } else {
                        alert(response.message || 'Transaction failed');
                    }
                },
                error: function() {
                    alert('Error processing withdrawal');
                },
                complete: function() {
                    submitBtn.prop('disabled', false).text('Request Withdrawal');
                }
            });
        });

        // Réinitialiser les formulaires lors de la fermeture des modals
        $('.modal').on('hidden.bs.modal', function() {
            $(this).find('form')[0].reset();
            $(this).find('button[type="submit"]').prop('disabled', false)
                .text($(this).attr('id') === 'depositModal' ? 'Confirm Deposit' : 'Request Withdrawal');
        });
    });

    function updateQRCode(currency) {
        const address = cryptoAddresses[currency];
        $('#walletAddress').text(address);
        $('#qrCode').attr('src', `https://api.qrserver.com/v1/create-qr-code/?data=${encodeURIComponent(address)}&size=200x200`);
    }

    function copyAddress() {
        const address = $('#walletAddress').text();
        navigator.clipboard.writeText(address)
            .then(() => alert('Address copied to clipboard!'))
            .catch(() => alert('Failed to copy address'));
    }
    </script>
</body>
</html>