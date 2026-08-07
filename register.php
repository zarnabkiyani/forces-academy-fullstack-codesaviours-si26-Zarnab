<?php
require_once 'config/db.php';
session_start();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name   = trim($_POST['full_name'] ?? '');
    $email       = trim($_POST['email'] ?? '');
    $password    = $_POST['password'] ?? '';
    $confirm     = $_POST['confirm_password'] ?? '';
    $roll_number = trim($_POST['roll_number'] ?? '');
    $class       = trim($_POST['class'] ?? '');

    if (empty($full_name) || empty($email) || empty($password) || empty($roll_number) || empty($class)) {
        $error = 'All fields are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        $hashed = password_hash($password, PASSWORD_DEFAULT);

        $sql = "INSERT INTO students (full_name, email, password, roll_number, class)
                VALUES (?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, 'sssss', $full_name, $email, $hashed, $roll_number, $class);

        if (mysqli_stmt_execute($stmt)) {
            header('Location: login.php?registered=1');
            exit;
        } else {
            $error = 'Registration failed. That email or roll number may already be in use.';
        }
        mysqli_stmt_close($stmt);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register — Forces Academy LMS</title>
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

        <p class="aside-quote">Every scholar begins<br>as a <em>registered</em> name<br>on the academy roll.</p>

        <div class="aside-foot">
            <div><strong>01</strong>Create account</div>
            <div><strong>02</strong>Sign in</div>
            <div><strong>03</strong>Begin studies</div>
        </div>
    </aside>

    <main class="auth-main">
        <div class="auth-card">
            <p class="eyebrow">Student Enrolment</p>
            <h1>Create your account</h1>
            <p class="lede">Register with the academy to access your courses, notices, and dashboard.</p>

            <?php if ($error): ?>
                <div class="alert-academy danger" role="alert">
                    <span>⚠</span><span><?php echo htmlspecialchars($error); ?></span>
                </div>
            <?php endif; ?>

            <form method="POST" action="register.php" data-guard novalidate>
                <div class="field">
                    <label for="full_name">Full name</label>
                    <input class="form-control" type="text" id="full_name" name="full_name" placeholder="e.g. Ayesha Khan" value="<?php echo isset($full_name) ? htmlspecialchars($full_name) : ''; ?>" required>
                </div>

                <div class="field">
                    <label for="email">Email address</label>
                    <input class="form-control" type="email" id="email" name="email" placeholder="you@example.com" value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>" required>
                </div>

                <div class="field-row">
                    <div class="field">
                        <label for="password">Password</label>
                        <div class="password-wrap">
                            <input class="form-control" type="password" id="password" name="password" placeholder="At least 6 characters" required>
                            <button type="button" class="toggle-pass" data-target="password">Show</button>
                        </div>
                    </div>
                    <div class="field">
                        <label for="confirm_password">Confirm password</label>
                        <div class="password-wrap">
                            <input class="form-control" type="password" id="confirm_password" name="confirm_password" placeholder="Re-enter password" required>
                            <button type="button" class="toggle-pass" data-target="confirm_password">Show</button>
                        </div>
                    </div>
                </div>
                <p class="hint" id="match-hint">&nbsp;</p>

                <div class="field-row">
                    <div class="field">
                        <label for="roll_number">Roll number</label>
                        <input class="form-control" type="text" id="roll_number" name="roll_number" placeholder="e.g. SI26-014" value="<?php echo isset($roll_number) ? htmlspecialchars($roll_number) : ''; ?>" required>
                    </div>
                    <div class="field">
                        <label for="class">Class</label>
                        <input class="form-control" type="text" id="class" name="class" placeholder="e.g. Full Stack — SI-26" value="<?php echo isset($class) ? htmlspecialchars($class) : ''; ?>" required>
                    </div>
                </div>

                <button type="submit" class="btn-academy" data-loading-text="Creating account...">Register</button>
            </form>

            <p class="auth-switch">Already have an account? <a href="login.php">Sign in</a></p>
        </div>
    </main>
</div>

<script src="js/main.js"></script>
</body>
</html>
