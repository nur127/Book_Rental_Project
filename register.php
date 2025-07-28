<?php
// register.php
session_start();
$conn = new mysqli("localhost", "root", "", "p2p");
$register_message = '';
$register_success = false;
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    try {
        $stmt = $conn->prepare("INSERT INTO users (name, username, email, password) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $name, $username, $email, $hashed_password);
        if ($stmt->execute()) {
            // Show modal and then redirect to login page
            $_SESSION['register_success_popup'] = true;
            header('Location: register.php');
            exit();
        } else {
            // Should not reach here, but fallback error
            $_SESSION['register_message'] = "Something went wrong. Please try again later.";
            $_SESSION['register_success'] = false;
        }
        $stmt->close();
    } catch (mysqli_sql_exception $e) {
        if ($e->getCode() == 1062) {
            $msg = $e->getMessage();
            if (strpos($msg, 'email') !== false) {
                $_SESSION['register_message'] = "An account with this email already exists. Please use a different email or <a href='login.php' class='alert-link'>login here</a>.";
            } elseif (strpos($msg, 'username') !== false || strpos($msg, 'users.username') !== false) {
                $_SESSION['register_message'] = "This username is already taken. Please choose another username.";
            } else {
                $_SESSION['register_message'] = "Duplicate entry detected. Please check your details.";
            }
        } else {
            $_SESSION['register_message'] = "Something went wrong. Please try again later.";
        }
        $_SESSION['register_success'] = false;
    }
    // After error, reload page to show message and clear on refresh
    header('Location: register.php');
    exit();
}

if (isset($_SESSION['register_message'])) {
    $register_message = $_SESSION['register_message'];
    $register_success = $_SESSION['register_success'] ?? false;
    unset($_SESSION['register_message'], $_SESSION['register_success']);
}
$show_success_popup = false;
if (isset($_SESSION['register_success_popup'])) {
    $show_success_popup = true;
    unset($_SESSION['register_success_popup']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Register - Book Rental Platform</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
<style>
  body {
    font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
    background-color: #f4f4f9;
    /* Remove flex and 100vh to avoid vertical scroll bar */
    margin: 0;
    padding: 0;
  }
  .container {
    background-color: #fff;
    padding: 24px 16px 18px 16px;
    border-radius: 8px;
    box-shadow: 0 8px 16px rgba(0,0,0,0.15);
    max-width: 370px;
    width: 100%;
    margin: 18px auto 18px auto;
  }
  h2 {
    text-align: center;
    margin-bottom: 16px;
    color: #333;
  }
  form {
    display: flex;
    flex-direction: column;
  }
  label {
    margin-bottom: 6px;
    font-weight: 600;
    color: #555;
  }
  input[type="text"],
  input[type="email"],
  input[type="password"] {
    padding: 10px;
    margin-bottom: 10px;
    border: 1px solid #ccc;
    border-radius: 4px;
    font-size: 1em;
    transition: border-color 0.2s;
  }
  input[type="text"]:focus,
  input[type="email"]:focus,
  input[type="password"]:focus {
    border-color: #007bff;
    outline: none;
  }
  button {
    padding: 10px;
    background-color: #007bff;
    color: white;
    border: none;
    border-radius: 4px;
    font-size: 1em;
    cursor: pointer;
    transition: background-color 0.2s;
  }
  button:hover {
    background-color: #0069d9;
  }
  .alert {
    margin-bottom: 18px;
    text-align: center;
    font-size: 1.05em;
    font-weight: 500;
    border-radius: 6px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.07);
  }
  p {
    margin-top: 12px;
    text-align: center;
  }
  a {
    color: #007bff;
    text-decoration: none;
  }
  a:hover {
    text-decoration: underline;
  }
</style>
</head>
<body>
<div class="w-100 text-center mb-2" style="margin-top:12px;">
  <a href="index.php" class="text-decoration-none d-inline-flex align-items-center gap-2">
    <span style="display:inline-block;width:40px;height:40px;vertical-align:middle;">
      <svg width="40" height="40" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
        <rect width="32" height="32" rx="8" fill="#007bff"/>
        <path d="M10 22V10h8a4 4 0 1 1 0 8h-8" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
      </svg>
    </span>
    <span class="fs-2 fw-bold text-primary" style="font-family: 'Segoe UI', Arial, sans-serif; letter-spacing:1px;">BookRental</span>
  </a>
</div>
<div class="container">
  <h2>Create an Account</h2>
  <?php if ($register_message && !$register_success): ?>
    <div class="alert alert-danger" role="alert">
      <?php echo $register_message; ?>
    </div>
  <?php endif; ?>
  <form method="POST" action="register.php">
      <label>Name:</label>
      <input type="text" name="name" required>

      <label>Username:</label>
      <input type="text" name="username" required>

      <label>Email:</label>
      <input type="email" name="email" required>

      <label>Password:</label>
      <input type="password" name="password" required>

      <button type="submit">Register</button>
  </form>
  <p>Already have an account? <a href="login.php">Login here</a></p>
</div>

<?php if ($show_success_popup): ?>
<!-- Registration Success Modal -->
<div class="modal fade show" id="registerSuccessModal" tabindex="-1" aria-labelledby="registerSuccessModalLabel" aria-modal="true" role="dialog" style="display:block; background:rgba(0,0,0,0.3);">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header border-0">
        <h5 class="modal-title w-100 text-center" id="registerSuccessModalLabel">Registration Successful</h5>
      </div>
      <div class="modal-body text-center">
        <div class="mb-3">
          <svg width="48" height="48" fill="#28a745" viewBox="0 0 16 16"><path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zM7 11.414l5.207-5.207-1.414-1.414L7 8.586 5.207 6.793 3.793 8.207 7 11.414z"/></svg>
        </div>
        <p class="mb-0">Your account has been created! Redirecting to login page...</p>
      </div>
    </div>
  </div>
</div>
<script>
  setTimeout(function() {
    window.location.href = 'login.php?registered=1';
  }, 1800);
</script>
<?php endif; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>