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

// Obtenir la liste des filleuls
$referrals_query = "
    SELECT u.email, u.created_at, 
           COALESCE(SUM(tc.amount), 0) as total_invested,
           COALESCE(SUM(tc.amount * 0.05), 0) as total_bonus
    FROM users u
    LEFT JOIN transactions_crypto tc ON u.id = tc.user_id
    WHERE u.referred_by = $1
    GROUP BY u.id, u.email, u.created_at";
$referrals_result = pg_query_params($conn, $referrals_query, array($user_id));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Referrals - Lazada Investment</title>
    <!-- Vos CSS habituels -->
    <style>
        .referral-link {
            background: #2d2d2d;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .stats-card {
            background: #2d2d2d;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <!-- Header comme dans les autres pages -->
    
    <div class="pt-120 pb-120">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="referral-link">
                        <h4>Your Referral Link</h4>
                        <div class="input-group">
                            <input type="text" class="form-control" id="referralLink" 
                                   value="<?php echo 'https://'.$_SERVER['HTTP_HOST'].'/register.php?ref='.$user['referral_code']; ?>" 
                                   readonly>
                            <div class="input-group-append">
                                <button class="btn btn-primary" onclick="copyReferralLink()">
                                    <i class="las la-copy"></i> Copy
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="stats-card">
                        <h4>Total Earnings</h4>
                        <h2 class="text-success">$<?php echo number_format($user['referral_earnings'], 2); ?></h2>
                    </div>
                </div>

                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">Your Referrals</h4>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Email</th>
                                            <th>Joined Date</th>
                                            <th>Total Invested</th>
                                            <th>Your Bonus</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($referral = pg_fetch_assoc($referrals_result)): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($referral['email']); ?></td>
                                            <td><?php echo date('Y-m-d', strtotime($referral['created_at'])); ?></td>
                                            <td>$<?php echo number_format($referral['total_invested'], 2); ?></td>
                                            <td>$<?php echo number_format($referral['total_bonus'], 2); ?></td>
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

    <script>
    function copyReferralLink() {
        var copyText = document.getElementById("referralLink");
        copyText.select();
        document.execCommand("copy");
        alert("Referral link copied!");
    }
    </script>
</body>
</html>