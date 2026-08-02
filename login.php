<?php
require_once 'config/db.php';
session_start();

$error = '';
$registered = isset($_GET['registered']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Please enter both email and password.';
    } else {
        $sql = "SELECT id, full_name, password FROM students WHERE email = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, 's', $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $student = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);

        if ($student && password_verify($password, $student['password'])) {
            $_SESSION['student_id']   = $student['id'];
            $_SESSION['student_name'] = $student['full_name'];
            header('Location: dashboard.php');
            exit;
        } else {
            $error = 'Invalid email or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In — Forces Academy LMS</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:wght@500;600;700&family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<div class="auth-shell">
    <aside class="auth-aside">
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

        <p class="aside-quote">Discipline opens<br>the door to<br><em>every</em> lesson within.</p>

        <div class="aside-foot">
            <div><strong>SI-26</strong>Batch</div>
            <div><strong>4</strong>Core tables</div>
            <div><strong>1</strong>Shared roll</div>
        </div>
    </aside>

    <main class="auth-main">
        <div class="auth-card">
            <p class="eyebrow">Student Portal</p>
            <h1>Welcome back</h1>
            <p class="lede">Sign in with your registered email to reach your dashboard.</p>

            <?php if ($registered): ?>
                <div class="alert-academy success" role="status">
                    <span>✓</span><span>Registration successful. You can sign in now.</span>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert-academy danger" role="alert">
                    <span>⚠</span><span><?php echo htmlspecialchars($error); ?></span>
                </div>
            <?php endif; ?>

            <form method="POST" action="login.php" data-guard novalidate>
                <div class="field">
                    <label for="email">Email address</label>
                    <input class="form-control" type="email" id="email" name="email" placeholder="you@example.com" value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>" required autofocus>
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

            <p class="auth-switch">New to the academy? <a href="register.php">Create an account</a></p>
            <p class="auth-switch">Academy staff? <a href="admin/login.php">Sign in to the admin panel</a></p>
        </div>
    </main>
</div>

<script src="js/main.js"></script>
</body>
</html>
