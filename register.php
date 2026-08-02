<?php
require_once 'auth.php';
require_once 'config.php';

if (is_logged_in()) {
    header('Location: hub.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm'] ?? '';

    if ($username === '' || $password === '') {
        $error = 'Please enter both username and password.';
    } elseif (strlen($username) < 3) {
        $error = 'Username must be at least 3 characters.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        $conn = get_db_connection();

        $stmt = $conn->prepare('SELECT id FROM users WHERE username = ?');
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $exists = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($exists) {
            $error = 'That username is already taken.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare('INSERT INTO users (username, password_hash) VALUES (?, ?)');
            $stmt->bind_param('ss', $username, $hash);
            if ($stmt->execute()) {
                $success = 'Account created. You can log in now.';
            } else {
                $error = 'Could not create account. Please try again.';
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
<title>Create account — <?php echo htmlspecialchars(APP_NAME); ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;600;700&family=Inter:wght@400;500;600;700&family=IBM+Plex+Mono:wght@500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="css/style.css">
</head>
<body class="auth-body">
  <div class="auth-card">
    <div class="brand auth-brand">
      <span class="brand-mark">◆</span>
      <div class="brand-text">
        <h1><?php echo htmlspecialchars(APP_NAME); ?></h1>
        <p>Sales analytics platform</p>
      </div>
    </div>

    <h2 class="auth-title">Create account</h2>
    <p class="auth-sub">Your uploads and searches stay private to your account.</p>

    <?php if ($error): ?>
      <div class="upload-message error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
      <div class="upload-message success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <form method="post" class="auth-form">
      <label class="auth-label">Username
        <input type="text" name="username" class="filter-input" minlength="3" required>
      </label>
      <label class="auth-label">Password
        <input type="password" name="password" class="filter-input" minlength="6" required>
      </label>
      <label class="auth-label">Confirm password
        <input type="password" name="confirm" class="filter-input" minlength="6" required>
      </label>
      <button type="submit" class="btn-primary auth-submit">Create account</button>
    </form>

    <p class="auth-footer-link">Already have an account? <a href="login.php">Log in</a></p>
  </div>
</body>
</html>
