<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$result = pg_query_params($conn, "SELECT email, balance FROM users WHERE id = $1", array($user_id));
$user = pg_fetch_assoc($result);

// Update the table creation SQL to include the 'type_invest' column with a default value
$sql = "CREATE TABLE IF NOT EXISTS transactions_crypto (
    id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES users(id),
    tx_hash VARCHAR(100) UNIQUE,
    currency VARCHAR(10) NOT NULL,
    amount DECIMAL(15,8) NOT NULL,
    status VARCHAR(20) DEFAULT 'pending',
    type_invest VARCHAR(20) DEFAULT 'investment',  -- Added 'type_invest' column with default value
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
pg_query($conn, $sql);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  header('Content-Type: application/json');
  
  if (isset($_POST['tx_hash'])) {
      try {
          $tx_hash = trim($_POST['tx_hash']);
          $currency = trim($_POST['currency']);
          $amount = floatval($_POST['amount']);
          $plan_id = intval($_POST['plan_id']);
          
          // Validation des données
          if (empty($tx_hash)) {
              throw new Exception("Transaction hash is required");
          }
          
          // Vérifier si le hash existe déjà
          $check = pg_query_params($conn, 
              "SELECT id FROM transactions_crypto WHERE tx_hash = $1", 
              array($tx_hash)
          );
          
          if (pg_num_rows($check) > 0) {
              throw new Exception("This transaction has already been submitted");
          }
          
          // Insérer la transaction
          $result = pg_query_params($conn,
              "INSERT INTO transactions_crypto (user_id, tx_hash, currency, amount, status, type_invest) 
               VALUES ($1, $2, $3, $4, 'pending', 'investment') RETURNING id",
              array($user_id, $tx_hash, $currency, $amount)
          );
          
          if (!$result) {
              throw new Exception(pg_last_error($conn));
          }
          
          echo json_encode([
              'success' => true,
              'message' => 'Transaction submitted successfully'
          ]);
          
      } catch (Exception $e) {
          echo json_encode([
              'success' => false,
              'message' => $e->getMessage()
          ]);
      }
      exit();
  }

}
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
        body {
            background-color: #1a1a1a;
            color: white;
        }
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
    </style>
</head>
<body>
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

    <section class="pt-120 pb-120">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-6 text-center">
                    <div class="section-header">
                        <h2 class="section-title"><span class="font-weight-normal">Investment</span> <span class="base--color">Plans</span></h2>
                        <p>To make a solid investment, you have to know where you are investing. Find a plan which is best for you.</p>
                        <p> Bonus offer your  First  deposit: receive 5% of the amount of the transaction as a bonus! </p>
                    </div>
                </div>
            </div>

            <div class="row justify-content-center mb-none-30">
                <?php
               $plans = [
                [
                    'id' => 1,
                    'name' => 'Slivestor',
                    'return_rate' => '6%',
                    'interval' => 'Every Day',
                    'duration' => 'For 30 Days',
                    'total_return' => 'Total 180% + Capital',
                    'amount' => '50'
                ],
                [
                    'id' => 2,
                    'name' => 'Bronze',
                    'return_rate' => '$1',
                    'interval' => 'Every Day', 
                    'duration' => 'For Lifetime',
                    'total_return' => 'Lifetime Earning',
                    'amount' => '1000'
                ],
                [
                    'id' => 3,
                    'name' => 'Black Horse',
                    'return_rate' => '10%',
                    'interval' => 'Every Week',
                    'duration' => 'For 40 Weeks', 
                    'total_return' => 'Total 400%',
                    'amount' => '200'
                ],
                [
                    'id' => 4,
                    'name' => 'Silver',
                    'return_rate' => '2%',
                    'interval' => 'Every Day',
                    'duration' => 'For 30 Days',
                    'total_return' => 'Total 60% + Capital',
                    'amount' => '50'
                ],
                [
                    'id' => 5,
                    'name' => 'Elephant',
                    'return_rate' => '1.354%',
                    'interval' => 'Every Day',
                    'duration' => 'For 30 Days',
                    'total_return' => 'Total 40.62% + Capital',
                    'amount' => '1000'
                ],
                [
                    'id' => 6,
                    'name' => 'Cobra',
                    'return_rate' => '$2',
                    'interval' => 'Every Hour',
                    'duration' => 'For 168 Hours',
                    'total_return' => 'Total 336 USD + Capital',
                    'amount' => '5000'
                ],
                [
                    'id' => 7,
                    'name' => 'Lion',
                    'return_rate' => '0.05%',
                    'interval' => 'Every Day',
                    'duration' => 'For Lifetime',
                    'total_return' => 'Lifetime Earning',
                    'amount' => '100'
                ],
                [
                    'id' => 8,
                    'name' => 'Tiger',
                    'return_rate' => '5%',
                    'interval' => 'Every Day',
                    'duration' => 'For Lifetime',
                    'total_return' => 'Lifetime Earning',
                    'amount' => '500'
                ]
             ];
                
                foreach ($plans as $plan): ?>
                <div class="col-xl-3 col-lg-4 col-md-6 mb-30">
                    <div class="package-card text-center">
                        <h4 class="package-card__title base--color mb-2"><?php echo $plan['name']; ?></h4>
                        <ul class="package-card__features mt-4">
                            <li>Return <?php echo $plan['return_rate']; ?></li>
                            <li><?php echo $plan['interval']; ?></li>
                            <li><?php echo $plan['duration']; ?></li>
                            <li><?php echo $plan['total_return']; ?></li>
                        </ul>
                        <div class="package-card__range mt-5 base--color">$<?php echo $plan['amount']; ?></div>
                        <button type="button" class="cmn-btn btn-md mt-4 investBtn" 
                                data-toggle="modal"
                                data-target="#cryptoModal"
                                data-amount="<?php echo $plan['amount']; ?>"
                                data-plan="<?php echo $plan['id']; ?>"
                                data-name="<?php echo $plan['name']; ?>">
                            Invest Now
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>

                <!-- Modal de paiement crypto -->
                <div class="modal fade" id="cryptoModal" tabindex="-1" role="dialog">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Investment Payment - <span id="planName"></span></h5>
                                <button type="button" class="close" data-dismiss="modal">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <div class="crypto-payment">
                                    <div class="currency-select mb-4">
                                        <label>Select Payment Method</label>
                                        <select class="form-control" id="cryptoCurrency">
                                            <option value="BTC">Bitcoin (BTC)</option>
                                            <option value="USDT">Binance</option>
                                        </select>
                                    </div>
                                    
                                    <div class="wallet-address text-center mb-4">
                                        <h6>Send payment to:</h6>
                                        <div class="qr-code my-3">
                                            <img src="" id="qrCode" alt="QR Code" class="img-fluid">
                                        </div>
                                        <div class="address-text">
                                            <code id="walletAddress" class="d-block p-2"></code>
                                            <button class="btn btn-primary mt-2" onclick="copyAddress()">
                                                <i class="las la-copy"></i> Copy Address
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <div class="payment-details">
                                        <div class="alert alert-info">
                                            <small>
                                                Amount to send: <span id="cryptoAmount">0.00</span> <span class="currency-label">BTC</span>
                                                <br>Your investment will be credited after confirmation
                                            </small>
                                        </div>
                                    </div>

                                    <div class="transaction-form mt-4">
                                        <div class="form-group">
                                            <label>Transaction Hash</label>
                                            <input type="text" class="form-control" id="txHash" placeholder="Enter your transaction hash (optional)">
                                        </div>
                                        <button class="cmn-btn w-100" onclick="submitTransaction()">
                                            Confirm Payment
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
     <!-- footer section start -->
     <footer class="footer bg_img" data-background="https://th.bing.com/th/id/R.b3a438ddcea17c1c921d6da3dcaf4f4c?rik=pHs6Uhs2ZKKHSA&pid=ImgRaw&r" alt="image"></a></footer>
  <div class="footer__top">
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-lg-12 text-center">
          <a href="#0" class="footer-logo"><img src="https://freelogopng.com/images/all_img/1685524601lazada-logo-png.png" alt="image"></a>
          <ul class="footer-short-menu d-flex flex-wrap justify-content-center mt-4">
            <li><a href="#0">Home</a></li>
            <li><a href="#0">Privacy & Policy</a></li>
            <li><a href="#0">Terms & Conditions</a></li>
          </ul>
        </div>
      </div>
    </div>
  </div>
  <div class="footer__bottom">
    <div class="container">
      <div class="row">
        <div class="col-md-6 text-md-left text-center">
          <p>© 2024 <a href="index.html" class="base--color">LAZADA</a>. All rights reserved</p>
        </div>
        <div class="col-md-6">
          <ul class="social-link-list d-flex flex-wrap justify-content-md-end justify-content-center">
            <li><a href="#0" data-toggle="tooltip" data-placement="top" title="facebook"><i class="lab la-facebook-f"></i></a></li>
            <li><a href="#0" data-toggle="tooltip" data-placement="top" title="twitter"><i class="lab la-twitter"></i></a></li>
            <li><a href="#0" data-toggle="tooltip" data-placement="top" title="pinterest"><i class="lab la-pinterest-p"></i></a></li>
            <li><a href="#0" data-toggle="tooltip" data-placement="top" title="pinterest"><i class="lab la-pinterest-in"></i></a></li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</footer>
<!-- footer section end -->

    <script src="assets/js/vendor/jquery-3.5.1.min.js"></script>
    <script src="assets/js/vendor/bootstrap.bundle.min.js"></script>
    <script src="assets/js/vendor/slick.min.js"></script>
    <script src="assets/js/vendor/wow.min.js"></script>
    <script src="assets/js/app.js"></script>

    <script>
    const cryptoAddresses = {
        'BTC': '1AqSt62gpYTLF3SaqiAqukMdFb5QiSfc7k', 
        'USDT': '0x92f474c4d3152d6a4e400ada508d28d0d9ffd821' // Replace with your real USDT address
    };

    let currentPlan = null;

    $('.investBtn').on('click', function() {
        currentPlan = {
            id: $(this).data('plan'),
            amount: $(this).data('amount'),
            name: $(this).data('name')
        };
        
        $('#planName').text(currentPlan.name);
        $('#cryptoAmount').text(currentPlan.amount);
        updateQRCode('BTC');
    });

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
    const txHash = document.getElementById('txHash').value.trim();
    if (!txHash) {
        alert('Please enter a transaction hash');
        return;
    }

    const currency = document.getElementById('cryptoCurrency').value;
    const submitBtn = document.querySelector('.transaction-form button');
    
    // Désactiver le bouton et montrer le chargement
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="las la-spinner la-spin"></i> Processing...';
    
    // Log des données envoyées
    console.log('Sending data:', {
        tx_hash: txHash,
        currency: currency,
        amount: currentPlan.amount,
        plan_id: currentPlan.id
    });
    
    $.ajax({
        url: window.location.href,
        method: 'POST',
        dataType: 'json',
        data: {
            tx_hash: txHash,
            currency: currency,
            amount: currentPlan.amount,
            plan_id: currentPlan.id
        },
        success: function(response) {
            console.log('Response:', response);
            if(response && response.success) {
                alert('Transaction submitted successfully');
                window.location.href = 'dashboard.php';
            } else {
                alert(response.message || 'Transaction failed');
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX Error:', {
                status: status,
                error: error,
                response: xhr.responseText
            });
            alert('Error submitting transaction. Please try again.');
        },
        complete: function() {
            submitBtn.disabled = false;
            submitBtn.innerHTML = 'Confirm Payment';
        }
    });

}
    $('#cryptoCurrency').change(function() {
        updateQRCode($(this).val());
    });

    $(document).ready(function() {
        if (currentPlan) {
            updateQRCode('BTC');
        }
    });
    </script>
</body>
</html>
