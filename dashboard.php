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

      <!-- inner hero start -->
      <section class="inner-hero bg_img" data-background="assets/images/bg/bg-1.jpg">
      <div class="container">
        <div class="row">
          <div class="col-lg-6">
            <h2 class="page-title">Dashboard</h2>
            <ul class="page-breadcrumb">
              <li><a href="index.html">Home</a></li>
              <li>Dashboard</li>
            </ul>
          </div>
        </div>
      </div>
    </section>
    <!-- inner hero end -->

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
                    $query = "SELECT COALESCE(SUM(amount), 0) as total FROM transactions WHERE user_id = $1 AND type = 'earning'";
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
  </div> <!-- page-wrapper end -->
    <!-- jQuery library -->
  <script src="assets/js/vendor/jquery-3.5.1.min.js"></script>
  <!-- bootstrap js -->
  <script src="assets/js/vendor/bootstrap.bundle.min.js"></script>
  <!-- slick slider js -->
  <script src="assets/js/vendor/slick.min.js"></script>
  <script src="assets/js/vendor/wow.min.js"></script>
  <script src="assets/js/contact.js"></script>
  <!-- dashboard custom js -->
  <script src="assets/js/app.js"></script>
</body>