<?php
require_once 'auth.php';
require_once 'config.php';

if (is_logged_in()) {
    header('Location: hub.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = 'Please enter both username and password.';
    } else {
        $conn = get_db_connection();
        $stmt = $conn->prepare('SELECT id, username, password_hash FROM users WHERE username = ?');
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();

        if ($user && password_verify($password, $user['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            header('Location: hub.php');
            exit;
        } else {
            $error = 'Incorrect username or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Log in — <?php echo htmlspecialchars(APP_NAME); ?></title>
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

    <h2 class="auth-title">Log in</h2>
    <p class="auth-sub">Each account keeps its own uploaded sales data.</p>

    <?php if ($error): ?>
      <div class="upload-message error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="post" class="auth-form">
      <label class="auth-label">Username
        <input type="text" name="username" class="filter-input" autofocus required>
      </label>
      <label class="auth-label">Password
        <input type="password" name="password" class="filter-input" required>
      </label>
      <button type="submit" class="btn-primary auth-submit">Log in</button>
    </form>

    <p class="auth-footer-link">No account yet? <a href="register.php">Create one</a></p>
  </div>
</body>
</html>
