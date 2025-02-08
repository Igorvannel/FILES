<?php
session_start();
require_once 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];
    
    $query = "SELECT id, password FROM users WHERE email = $1";
    $result = pg_query_params($conn, $query, array($email));
    
    if ($row = pg_fetch_assoc($result)) {
        if (password_verify($password, $row['password'])) {
            $_SESSION['user_id'] = $row['id'];
            header("Location: dashboard.php");
            exit();
        }
    }
    $_SESSION['error'] = "Email ou mot de passe incorrect";
}
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
                <p>Lazada is an e-commerce platform in Southeast Asia that aims to facilitate consumer investment in Lazada Group.</p>
              </div>
              <div class="account-card__body">
                <h3 class="text-center">Login</h3>
                <?php if(isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
                <?php endif; ?>
                <?php if(isset($_SESSION['success'])): ?>
                    <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
                <?php endif; ?>
                <form class="mt-4" method="POST" action="">
                  <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control" required>
                  </div>
                  <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" class="form-control" required>
                  </div>
                  <div class="form-row">
                    <div class="col-sm-6">
                      <div class="form-group form-check">
                        <input type="checkbox" class="form-check-input" id="exampleCheck1">
                        <label class="form-check-label" for="exampleCheck1">Remember me</label>
                      </div>
                    </div>
                    <div class="col-sm-6 text-sm-right">
                      <p class="f-size-14">Haven't an account? <a href="registration.php" class="base--color">Sign Up</a></p>
                    </div>
                  </div>
                  <div class="mt-3">
                    <button type="submit" class="cmn-btn">Login Now</button>
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