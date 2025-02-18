<?php
session_start();
require_once 'config.php';

function generateReferralCode($length = 8) {
    $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $code = '';
    for ($i = 0; $i < $length; $i++) {
        $code .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $code;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $fullname = filter_input(INPUT_POST, 'fullname', FILTER_SANITIZE_STRING);
  $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
  $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
  $referral_code = generateReferralCode();
  
  // Récupérer le code de parrainage soit de l'URL soit du formulaire
  $referred_by = isset($_GET['ref']) ? $_GET['ref'] : (
      !empty($_POST['referral_code']) ? $_POST['referral_code'] : null
  );
  
  // Vérifier si l'email existe déjà
  $check_email = pg_query_params($conn, 
      "SELECT id FROM users WHERE email = $1", 
      array($email)
  );
  
  if (pg_num_rows($check_email) > 0) {
      $_SESSION['error'] = "Cet email est déjà enregistré.";
  } else {
      // Début de la transaction
      pg_query($conn, "BEGIN");
      
      try {
          // Insérer le nouvel utilisateur
          if ($referred_by) {
              // Vérifier si le code de parrainage existe
              $ref_query = pg_query_params($conn, 
                  "SELECT id FROM users WHERE referral_code = $1", 
                  array($referred_by)
              );
              $referrer = pg_fetch_assoc($ref_query);
              
              if ($referrer) {
                  $result = pg_query_params($conn,
                      "INSERT INTO users (fullname, email, password, referral_code, referred_by) 
                       VALUES ($1, $2, $3, $4, $5) RETURNING id",
                      array($fullname, $email, $password, $referral_code, $referrer['id'])
                  );
              } else {
                  throw new Exception("Code de parrainage invalide");
              }
          } else {
              $result = pg_query_params($conn,
                  "INSERT INTO users (fullname, email, password, referral_code) 
                   VALUES ($1, $2, $3, $4) RETURNING id",
                  array($fullname, $email, $password, $referral_code)
              );
          }
          
          if ($result) {
              pg_query($conn, "COMMIT");
              $_SESSION['success'] = "Inscription réussie! Veuillez vous connecter.";
              header("Location: login.php");
              exit();
          } else {
              throw new Exception("Erreur lors de l'inscription");
          }
      } catch (Exception $e) {
          pg_query($conn, "ROLLBACK");
          $_SESSION['error'] = "Erreur: " . $e->getMessage();
      }
  }
}
// Si l'utilisateur arrive via un lien de parrainage
$ref = isset($_GET['ref']) ? $_GET['ref'] : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>LAZADA'S INVESTMENT</title>
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

  <div class="scroll-to-top">
    <span class="scroll-icon">
      <i class="fa fa-rocket" aria-hidden="true"></i>
    </span>
  </div>

  <div class="full-wh">
    <div class="bg-animation">
      <div id='stars'></div>
      <div id='stars2'></div>
      <div id='stars3'></div>
      <div id='stars4'></div>
    </div>
  </div>
  
  <div class="page-wrapper">
    <div class="account-section bg_img" data-background="https://cdn4.iconfinder.com/data/icons/cryptocoins/227/USDT-alt-512.png">
      <div class="container">
        <div class="row justify-content-center">
          <div class="col-xl-5 col-lg-7">
            <div class="account-card">
              <div class="account-card__header bg_img overlay--one" data-background="assets/images/bg/bg-6.jpg">
                <h2 class="section-title">Welcome to <span class="base--color">LAZADA</span></h2>
                <p>Lazada is primarily known as a leading e-commerce platform in Southeast Asia, but it has also ventured into the world of crypto investments.</p>
              </div>
              <div class="account-card__body">
                <h3 class="text-center">Create an Account</h3>
                <?php if(isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
                <?php endif; ?>
                <?php if(isset($_SESSION['success'])): ?>
                    <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
                <?php endif; ?>
                <form class="mt-4" method="POST" action="<?php echo $ref ? "?ref=".$ref : ""; ?>">
                  <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="fullname" class="form-control" required>
                  </div>
                  <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" name="email" class="form-control" required>
                  </div>
                  <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" class="form-control" required>
                  </div>
                  <div class="form-group">
                     <label>Referral Code (Optional)</label>
                     <input type="text" name="referral_code" class="form-control" 
                       value="<?php echo isset($_GET['ref']) ? htmlspecialchars($_GET['ref']) : ''; ?>" 
                            placeholder="Enter referral code if you have one">
                  </div>
                  <div class="form-row">
                    <div class="col-sm-6">
                      <div class="form-group form-check">
                        <input type="checkbox" class="form-check-input" id="exampleCheck1" required>
                        <label class="form-check-label" for="exampleCheck1">Remember me</label>
                      </div>
                    </div>
                    <div class="col-sm-6 text-sm-right">
                      <p class="f-size-14">Have an account? <a href="login.php" class="base--color">Login</a></p>
                    </div>
                  </div>
                  <div class="mt-3">
                    <button type="submit" class="cmn-btn">SignUp Now</button>
                  </div>
                </form>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script src="assets/js/vendor/jquery-3.5.1.min.js"></script>
  <script src="assets/js/vendor/bootstrap.bundle.min.js"></script>
  <script src="assets/js/vendor/slick.min.js"></script>
  <script src="assets/js/vendor/wow.min.js"></script>
  <script src="assets/js/contact.js"></script>
  <script src="assets/js/app.js"></script>
</body>
</html>