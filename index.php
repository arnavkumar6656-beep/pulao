<?php
session_start();
require 'db.php';

if (isset($_SESSION['user_id'])) {
    header("Location: home.php");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = "Please fill in all fields.";
    } else {
        if ($action == 'login') {
            $stmt = $conn->prepare("SELECT id, password FROM users WHERE username = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($user = $result->fetch_assoc()) {
                if (password_verify($password, $user['password'])) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $username;
                    header("Location: home.php");
                    exit;
                } else {
                    $error = "Invalid password.";
                }
            } else {
                $error = "User not found.";
            }
            $stmt->close();
        } elseif ($action == 'register') {
            $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $stmt->store_result();
            if ($stmt->num_rows > 0) {
                $error = "Username already exists.";
            } else {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt2 = $conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
                $stmt2->bind_param("ss", $username, $hashed_password);
                if ($stmt2->execute()) {
                    $_SESSION['user_id'] = $conn->insert_id;
                    $_SESSION['username'] = $username;
                    header("Location: home.php");
                    exit;
                } else {
                    $error = "Error creating account.";
                }
                $stmt2->close();
            }
            $stmt->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pulao - Login</title>
    <script>
        if (localStorage.getItem('theme') === 'light') {
            document.documentElement.classList.add('light-mode');
        }
    </script>
    <link rel="stylesheet" href="style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>

<body>
    <div class="auth-container">
        <!-- Login Form -->
        <div class="auth-card" id="login-form">
            <h2>Login to Pulao</h2>
            <?php if ($error && $_POST['action'] == 'login'): ?>
                <div class="error-msg"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <form method="POST" action="index.php" onsubmit="return validateAuthForm(this)">
                <input type="hidden" name="action" value="login">
                <div class="form-group">
                    <label>Username</label>
                    <input type="text" name="username" required>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" required>
                </div>
                <button type="submit" class="btn">Login</button>
            </form>
            <div class="auth-toggle">
                Don't have an account? <a onclick="toggleAuth('register')">Register</a>
            </div>
        </div>

        <!-- Register Form -->
        <div class="auth-card" id="register-form" style="display: none;">
            <h2>Create Account</h2>
            <?php if ($error && $_POST['action'] == 'register'): ?>
                <div class="error-msg"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <form method="POST" action="index.php" onsubmit="return validateAuthForm(this)">
                <input type="hidden" name="action" value="register">
                <div class="form-group">
                    <label>Username</label>
                    <input type="text" name="username" required>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" required>
                </div>
                <button type="submit" class="btn">Register</button>
            </form>
            <div class="auth-toggle">
                Already have an account? <a onclick="toggleAuth('login')">Login</a>
            </div>
        </div>
    </div>

    <script src="script.js"></script>
    <script>
        // Show register form if there was a register error
        <?php if ($error && isset($_POST['action']) && $_POST['action'] == 'register'): ?>
            $('#login-form').hide();
            $('#register-form').show();
        <?php endif; ?>
    </script>
</body>

</html>