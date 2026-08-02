<?php
require_once 'includes/auth.php';
require_once '../config/db.php';

// Already signed in as admin? Skip straight to the dashboard.
if (!empty($_SESSION['admin_id']) && !empty($_SESSION['admin_role'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password.';
    } else {
        $sql  = "SELECT id, username, password FROM admins WHERE username = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, 's', $username);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $admin  = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);

        if ($admin && password_verify($password, $admin['password'])) {
            // Regenerate the session id on login to prevent session fixation.
            session_regenerate_id(true);

            $_SESSION['admin_id']       = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];
            $_SESSION['admin_role']     = 'admin'; // role flag, per Week 4 spec

            header('Location: dashboard.php');
            exit;
        } else {
            $error = 'Invalid username or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Sign In — Forces Academy LMS</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:wght@500;600;700&family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<div class="auth-shell">
    <aside class="auth-aside admin-aside">
        <div class="brand-row">
            <span class="crest" aria-hidden="true">
                <svg viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M32 3 L58 13 V30 C58 45 47 55 32 61 C17 55 6 45 6 30 V13 Z" fill="#16305F" stroke="#E4C97A" stroke-width="2"/>
                    <path d="M32 14 L32 44 M20 24 L44 24" stroke="#E4C97A" stroke-width="2.5" stroke-linecap="round"/>
                    <circle cx="32" cy="34" r="5" fill="#E4C97A"/>
                </svg>
            </span>
            <div>
                <p class="brand-name">Forces Academy</p>
                <p class="brand-sub">Learning Management System</p>
            </div>
        </div>

        <p class="aside-quote">Those who guide others<br>must first master<br><em>every</em> detail.</p>

        <div class="aside-foot">
            <div><strong>Admin</strong>Access</div>
            <div><strong>6</strong>Panel modules</div>
            <div><strong>1</strong>Control center</div>
        </div>
    </aside>

    <main class="auth-main">
        <div class="auth-card">
            <span class="admin-pill">Staff Only</span>
            <p class="eyebrow">Admin Portal</p>
            <h1>Sign in to manage</h1>
            <p class="lede">Enter your admin credentials to reach the control panel.</p>

            <?php if ($error): ?>
                <div class="alert-academy danger" role="alert">
                    <span>⚠</span><span><?php echo htmlspecialchars($error); ?></span>
                </div>
            <?php endif; ?>

            <form method="POST" action="login.php" data-guard novalidate>
                <div class="field">
                    <label for="username">Username</label>
                    <input class="form-control" type="text" id="username" name="username" placeholder="admin" value="<?php echo isset($username) ? htmlspecialchars($username) : ''; ?>" required autofocus>
                </div>

                <div class="field">
                    <label for="password">Password</label>
                    <div class="password-wrap">
                        <input class="form-control" type="password" id="password" name="password" placeholder="Your password" required>
                        <button type="button" class="toggle-pass" data-target="password">Show</button>
                    </div>
                </div>

                <button type="submit" class="btn-academy" data-loading-text="Signing in...">Sign in</button>
            </form>

            <p class="auth-switch"><a href="../login.php">← Back to student sign in</a></p>
        </div>
    </main>
</div>

<script src="../js/main.js"></script>
</body>
</html>
