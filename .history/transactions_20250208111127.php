<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Vérifier la connexion à la base de données
if (!$conn) {
    echo "Échec de la connexion à la base de données.";
    exit;
}

// Récupérer l'email de l'utilisateur
$result = pg_query_params($conn, "SELECT email FROM users WHERE id = $1", array($user_id));
if (!$result) {
    echo "Erreur dans la récupération des données de l'utilisateur: " . pg_last_error($conn);
    exit;
}
$user = pg_fetch_assoc($result);

// Requête pour récupérer les transactions crypto
$query_crypto = "SELECT * 
                 FROM transactions_crypto t
                 ORDER BY t.created_at DESC";

// Exécution de la requête sans paramètre
$transactions = pg_query($conn, $query_crypto);

// Gestion des erreurs de la requête
if (!$transactions) {
    echo "Erreur dans la requête SQL: " . pg_last_error($conn);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaction History - Lazada Investment</title>
    <link rel="stylesheet" href="assets/css/vendor/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/all.min.css">
    <link rel="stylesheet" href="assets/css/line-awesome.min.css">
    <link rel="stylesheet" href="assets/css/vendor/animate.min.css">
    <link rel="stylesheet" href="assets/css/main.css">
    <style>
        body { background-color: #1a1a1a; color: white; }
        .table { color: white; }
        .card { background-color: #2d2d2d; border: 1px solid #4d4d4d; }
        .table td, .table th { border-top: 1px solid #4d4d4d; }
        .badge-pending { background-color: #ffc107; color: #000; }
        .badge-completed { background-color: #28a745; }
        .badge-failed { background-color: #dc3545; }
        .tx-hash { font-family: monospace; color: #ffc107; }
    </style>
</head>
<body>
    <header class="header">
        <div class="header__bottom">
            <div class="container">
                <nav class="navbar navbar-expand-xl p-0 align-items-center">
                    <a class="site-logo site-title" href="dashboard.php">
                        <img src="assets/images/logoicon.png" alt="site-logo">
                        <span style="color: white; font-size: 24px; margin-left: 10px;">Lazada</span>
                    </a>
                    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent">
                        <span class="menu-toggle"></span>
                    </button>
                    <div class="collapse navbar-collapse" id="navbarSupportedContent">
                        <ul class="navbar-nav main-menu m-auto">
                            <li><a href="dashboard.php">Dashboard</a></li>
                            <li><a href="plan.php">Invest</a></li>
                            <li><a class="active" href="transactions.php">Transactions</a></li>
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

    <div class="pt-120 pb-120">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h4 class="card-title mb-0">Transaction History</h4>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Type</th>
                                            <th>Amount</th>
                                            <th>Currency</th>
                                            <th>Status</th>
                                            <th>Transaction ID</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        if (pg_num_rows($transactions) > 0) {
                                            while ($row = pg_fetch_assoc($transactions)) {
                                        ?>
                                        <tr>
                                            <td><?php echo date('Y-m-d H:i', strtotime($row['created_at'])); ?></td>
                                            <td>
                                                <span class="badge badge-<?php echo $row['type_invest'] == 'investment' ? 'info' : 'success'; ?>">
                                                    <?php echo $row['type_invest']; ?>
                                                </span>
                                            </td>
                                            <td><?php echo number_format($row['amount'], 2); ?></td>
                                            <td><?php echo $row['currency']; ?></td>
                                            <td>
                                                <span class="badge badge-<?php echo $row['status']; ?>">
                                                    <?php echo ucfirst($row['status']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ($row['tx_hash']): ?>
                                                    <span class="tx-hash"><?php echo substr($row['tx_hash'], 0, 16); ?>...</span>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <?php 
                                            }
                                        } else {
                                            echo "<tr><td colspan='6' class='text-center'>No transactions found.</td></tr>";
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer class="footer bg_img">
        <div class="footer__bottom">
            <div class="container">
                <div class="row">
                    <div class="col-md-6 text-md-left text-center">
                        <p>© 2024 <a href="dashboard.php" class="base--color">LAZADA</a>. All rights reserved</p>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <script src="assets/js/vendor/jquery-3.5.1.min.js"></script>
    <script src="assets/js/vendor/bootstrap.bundle.min.js"></script>
    <script src="assets/js/app.js"></script>
</body>
</html>
