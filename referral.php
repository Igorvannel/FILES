<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Obtenir les informations de l'utilisateur
$user_query = "SELECT email, referral_code, referral_earnings FROM users WHERE id = $1";
$user_result = pg_query_params($conn, $user_query, array($user_id));
$user = pg_fetch_assoc($user_result);

// Obtenir la liste des filleuls et leurs investissements
$referrals_query = "
    SELECT u.email, u.created_at, 
           COALESCE(SUM(tc.amount), 0) as total_invested,
           COALESCE(SUM(tc.amount * 0.05), 0) as total_bonus
    FROM users u
    LEFT JOIN transactions_crypto tc ON u.id = tc.user_id
    WHERE u.referred_by = $1
    GROUP BY u.id, u.email, u.created_at
    ORDER BY u.created_at DESC";
$referrals_result = pg_query_params($conn, $referrals_query, array($user_id));

function maskEmail($email) {
    $parts = explode('@', $email);
    if (count($parts) !== 2) return $email;
    
    $name = $parts[0];
    $domain = $parts[1];
    
    $nameLength = strlen($name);
    $visibleLength = floor($nameLength / 2);
    
    $maskedName = substr($name, 0, $visibleLength) . str_repeat('*', $nameLength - $visibleLength);
    
    return $maskedName . '@' . $domain;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LAZADA'S INVESTMENT - Referrals</title>
    <link rel="icon" type="image/png" href="assets/images/favicon.png" sizes="16x16">
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
        .referral-box {
            background: #2d2d2d;
            padding: 25px;
            border-radius: 5px;
            margin-bottom: 30px;
            border: 1px solid #4d4d4d;
        }
        .referral-link-input {
            background: #1a1a1a;
            color: #ffc107;
            border: 1px solid #4d4d4d;
            padding: 15px;
            border-radius: 5px;
            font-family: monospace;
            margin: 10px 0;
        }
        .stats-card {
            background: #2d2d2d;
            border: 1px solid #4d4d4d;
            border-radius: 5px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .table { 
            color: white; 
        }
        .table td, .table th { 
            border-top: 1px solid #4d4d4d; 
        }
        .badge-success {
            background-color: #28a745;
        }
        .badge-info {
            background-color: #17a2b8;
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
                            <li><a class="active" href="referral.php">Referrals</a></li>
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
                    <h2 class="page-title">Referral Program</h2>
                    <ul class="page-breadcrumb">
                        <li><a href="dashboard.php">Home</a></li>
                        <li>Referrals</li>
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
                    <div class="col-xl-12 mb-30">
    <div class="referral-box">
        <h4 class="mb-4"><i class="las la-link base--color"></i> Your Referral Code</h4>
        <p>Share this code with your friends and earn 5% of their investments!</p>
        <div class="input-group">
            <input type="text" class="form-control referral-link-input" id="referralLink" 
                   value="<?php echo $user['referral_code']; ?>" 
                   readonly>
            <div class="input-group-append">
                <button class="cmn-btn" onclick="copyReferralLink()">
                    <i class="las la-copy"></i> Copy Code
                </button>
            </div>
        </div>
    </div>
</div>
                        <div class="col-xl-4 col-sm-6 mb-30">
                            <div class="d-widget d-flex flex-wrap">
                                <div class="col-8">
                                    <span class="caption">Total Referral Earnings</span>
                                    <h4 class="currency-amount">$<?php echo number_format($user['referral_earnings'], 2); ?></h4>
                                    <?php if ($user['referral_earnings'] >= 50): ?>
                                        <button class="cmn-btn btn-sm mt-2" onclick="transferEarnings()">
                                            <i class="las la-exchange-alt"></i> Transfer to Balance
                                        </button>
                                    <?php else: ?>
                                        <small class="text-muted">Minimum $50 required to transfer</small>
                                    <?php endif; ?>
                                </div>
                                <div class="col-4">
                                    <div class="icon ml-auto">
                                        <i class="las la-gift"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-50">
                        <div class="col-lg-12">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title">Your Referrals</h4>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive--md">
                                        <table class="table style--two">
                                            <thead>
                                                <tr>
                                                    <th>User</th>
                                                    <th>Joined Date</th>
                                                    <th>Total Invested</th>
                                                    <th>Your Bonus</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php 
                                                if (pg_num_rows($referrals_result) > 0):
                                                    while ($referral = pg_fetch_assoc($referrals_result)): 
                                                ?>
                                                <tr>
                                                <td data-label="User">
    <span class="base--color">
        <i class="las la-user-circle"></i> 
        <?php echo htmlspecialchars(maskEmail($referral['email'])); ?>
    </span>
</td>
                                                    <td data-label="Joined Date">
                                                        <?php echo date('d M, Y', strtotime($referral['created_at'])); ?>
                                                    </td>
                                                    <td data-label="Total Invested">
                                                        $<?php echo number_format($referral['total_invested'], 2); ?>
                                                    </td>
                                                    <td data-label="Your Bonus">
                                                        <span class="text-success">
                                                            $<?php echo number_format($referral['total_bonus'], 2); ?>
                                                        </span>
                                                    </td>
                                                </tr>
                                                <?php 
                                                    endwhile; 
                                                else:
                                                ?>
                                                <tr>
                                                    <td colspan="4" class="text-center">No referrals yet</td>
                                                </tr>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

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

    <script src="assets/js/vendor/jquery-3.5.1.min.js"></script>
    <script src="assets/js/vendor/bootstrap.bundle.min.js"></script>
    <script src="assets/js/vendor/slick.min.js"></script>
    <script src="assets/js/vendor/wow.min.js"></script>
    <script src="assets/js/app.js"></script>

    <script>
    function copyReferralLink() {
        var copyText = document.getElementById("referralLink");
        copyText.select();
        document.execCommand("copy");
        alert("Referral link copied to clipboard!");
    }

    function transferEarnings() {
        if (confirm('Are you sure you want to transfer your referral earnings to your main balance?')) {
            $.ajax({
                url: 'process_transfer.php',
                method: 'POST',
                data: {
                    action: 'transfer_earnings'
                },
                success: function(response) {
                    if (response.success) {
                        alert('Transfer successful!');
                        location.reload();
                    } else {
                        alert(response.message || 'Transfer failed');
                    }
                },
                error: function() {
                    alert('Error processing transfer');
                }
            });
        }
    }
    </script>
</body>
</html>