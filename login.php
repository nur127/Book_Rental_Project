<?php
// login.php
session_start();
$conn = new mysqli("localhost", "root", "", "p2p");

$login_error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username_or_email = $_POST['username_or_email'];
    $password = $_POST['password'];

    // Prepare statement to prevent SQL injection
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
    $stmt->bind_param("ss", $username_or_email, $username_or_email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        // Verify password
        if (password_verify($password, $user['password'])) {
            // Successful login
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            header("Location: index.php");
            exit;
        } else {
            $login_error = "Incorrect password. Please try again.";
        }
    } else {
        $login_error = "No account found with that username or email.";
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Login - Book Rental Platform</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
<style>
  body {
    font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
    background-color: #f4f4f9;
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
  }
  .container {
    background-color: #fff;
    padding: 40px 30px;
    border-radius: 8px;
    box-shadow: 0 8px 16px rgba(0,0,0,0.15);
    max-width: 400px;
    width: 100%;
  }
  h2 {
    text-align: center;
    margin-bottom: 24px;
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
  input[type="password"] {
    padding: 12px;
    margin-bottom: 16px;
    border: 1px solid #ccc;
    border-radius: 4px;
    font-size: 1em;
    transition: border-color 0.2s;
  }
  input[type="text"]:focus,
  input[type="password"]:focus {
    border-color: #007bff;
    outline: none;
  }
  button {
    padding: 14px;
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
<div class="container">
  <h2>Login to Your Account</h2>
  <?php if ($login_error): ?>
    <!-- Login Error Modal -->
    <div class="modal fade show" id="loginErrorModal" tabindex="-1" aria-labelledby="loginErrorModalLabel" aria-modal="true" role="dialog" style="display:block; background:rgba(0,0,0,0.3);">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header border-0">
            <h5 class="modal-title w-100 text-center text-danger" id="loginErrorModalLabel">Login Error</h5>
          </div>
          <div class="modal-body text-center">
            <div class="mb-3">
              <svg width="48" height="48" fill="#dc3545" viewBox="0 0 16 16"><path d="M8 1a7 7 0 1 0 0 14A7 7 0 0 0 8 1zm0 12.938A5.938 5.938 0 1 1 8 2.062a5.938 5.938 0 0 1 0 11.876zM7.002 4a1 1 0 1 1 2 0v4a1 1 0 0 1-2 0V4zm1 7a1 1 0 1 1 0-2 1 1 0 0 1 0 2z"/></svg>
            </div>
            <p class="mb-0"><?php echo $login_error; ?></p>
          </div>
          <div class="modal-footer border-0 justify-content-center">
            <button type="button" class="btn btn-danger px-4" onclick="document.getElementById('loginErrorModal').style.display='none';document.body.classList.remove('modal-open');">Close</button>
          </div>
        </div>
      </div>
    </div>
    <script>
      document.body.classList.add('modal-open');
    </script>
  <?php endif; ?>
  <form method="POST" action="login.php">
      <label>Username or Email:</label>
      <input type="text" name="username_or_email" required>

      <label>Password:</label>
      <input type="password" name="password" required>

      <button type="submit">Login</button>
  </form>
  <p>Don't have an account? <a href="register.php">Register here</a></p>
</div>
</body>
</html>