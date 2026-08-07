<?php
session_start();

if (!isset($_SESSION['student_id'])) {
    header('Location: login.php');
    exit;
}

require_once 'config/db.php';

$active_page = 'profile';
$page_title  = 'My Profile';

$profile_error   = '';
$profile_success = '';
$password_error  = '';
$password_success = '';

$student_id = $_SESSION['student_id'];

// -----------------------------------------------------------------
// Handle "edit profile" form (name + email)
// -----------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $full_name = trim($_POST['full_name'] ?? '');
    $email     = trim($_POST['email'] ?? '');

    if ($full_name === '' || $email === '') {
        $profile_error = 'Name and email cannot be empty.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $profile_error = 'Please enter a valid email address.';
    } else {
        // Make sure the email isn't already used by another student
        $stmt = mysqli_prepare($conn, "SELECT id FROM students WHERE email = ? AND id != ?");
        mysqli_stmt_bind_param($stmt, 'si', $email, $student_id);
        mysqli_stmt_execute($stmt);
        $taken = mysqli_stmt_get_result($stmt)->fetch_assoc();
        mysqli_stmt_close($stmt);

        if ($taken) {
            $profile_error = 'That email is already in use by another account.';
        } else {
            $stmt = mysqli_prepare($conn, "UPDATE students SET full_name = ?, email = ? WHERE id = ?");
            mysqli_stmt_bind_param($stmt, 'ssi', $full_name, $email, $student_id);
            if (mysqli_stmt_execute($stmt)) {
                $_SESSION['student_name'] = $full_name; // keep session data in sync
                $profile_success = 'Profile updated successfully.';
            } else {
                $profile_error = 'Could not update your profile. Please try again.';
            }
            mysqli_stmt_close($stmt);
        }
    }
}

// -----------------------------------------------------------------
// Handle "change password" form
// -----------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'] ?? '';
    $new_password      = $_POST['new_password'] ?? '';
    $confirm_password  = $_POST['confirm_password'] ?? '';

    $stmt = mysqli_prepare($conn, "SELECT password FROM students WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $student_id);
    mysqli_stmt_execute($stmt);
    $row = mysqli_stmt_get_result($stmt)->fetch_assoc();
    mysqli_stmt_close($stmt);

    if (!$row || !password_verify($current_password, $row['password'])) {
        $password_error = 'Current password is incorrect.';
    } elseif (strlen($new_password) < 6) {
        $password_error = 'New password must be at least 6 characters.';
    } elseif ($new_password !== $confirm_password) {
        $password_error = 'New password and confirmation do not match.';
    } else {
        $hashed = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = mysqli_prepare($conn, "UPDATE students SET password = ? WHERE id = ?");
        mysqli_stmt_bind_param($stmt, 'si', $hashed, $student_id);
        if (mysqli_stmt_execute($stmt)) {
            $password_success = 'Password changed successfully.';
        } else {
            $password_error = 'Could not change your password. Please try again.';
        }
        mysqli_stmt_close($stmt);
    }
}

// -----------------------------------------------------------------
// Always load the latest profile details to display / prefill
// -----------------------------------------------------------------
$stmt = mysqli_prepare($conn, "SELECT full_name, email, roll_number, class FROM students WHERE id = ?");
mysqli_stmt_bind_param($stmt, 'i', $student_id);
mysqli_stmt_execute($stmt);
$student = mysqli_stmt_get_result($stmt)->fetch_assoc();
mysqli_stmt_close($stmt);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<?php include 'includes/head.php'; ?>
</head>
<body>

<div class="app-shell">
    <?php include 'includes/sidebar.php'; ?>

    <main class="main-content">
        <div class="page-head">
            <div>
                <p class="eyebrow">Account</p>
                <h1>My Profile</h1>
                <p class="lede">View your details and keep your account information up to date.</p>
            </div>
        </div>

        <div class="panel">
            <h3>Current Details</h3>
            <div class="admin-form-grid">
                <div class="field">
                    <label>Full name</label>
                    <p class="mono" style="margin:0;"><?php echo htmlspecialchars($student['full_name']); ?></p>
                </div>
                <div class="field">
                    <label>Email</label>
                    <p class="mono" style="margin:0;"><?php echo htmlspecialchars($student['email']); ?></p>
                </div>
                <div class="field">
                    <label>Roll number</label>
                    <p class="mono" style="margin:0;"><?php echo htmlspecialchars($student['roll_number']); ?></p>
                </div>
                <div class="field">
                    <label>Class</label>
                    <p class="mono" style="margin:0;"><?php echo htmlspecialchars($student['class']); ?></p>
                </div>
            </div>
        </div>

        <div class="panel">
            <h3>Edit Profile</h3>

            <?php if ($profile_success): ?>
                <div class="alert-academy success" role="status"><span>✓</span><span><?php echo htmlspecialchars($profile_success); ?></span></div>
            <?php endif; ?>
            <?php if ($profile_error): ?>
                <div class="alert-academy danger" role="alert"><span>⚠</span><span><?php echo htmlspecialchars($profile_error); ?></span></div>
            <?php endif; ?>

            <form method="POST" action="profile.php" data-guard novalidate>
                <div class="admin-form-grid">
                    <div class="field">
                        <label for="full_name">Full name</label>
                        <input class="form-control" type="text" id="full_name" name="full_name"
                               value="<?php echo htmlspecialchars($student['full_name']); ?>" required>
                    </div>
                    <div class="field">
                        <label for="email">Email</label>
                        <input class="form-control" type="email" id="email" name="email"
                               value="<?php echo htmlspecialchars($student['email']); ?>" required>
                    </div>
                </div>
                <div class="admin-form-actions">
                    <button type="submit" name="update_profile" value="1" class="btn-academy" data-loading-text="Saving...">Update Profile</button>
                </div>
            </form>
        </div>

        <div class="panel">
            <h3>Change Password</h3>

            <?php if ($password_success): ?>
                <div class="alert-academy success" role="status"><span>✓</span><span><?php echo htmlspecialchars($password_success); ?></span></div>
            <?php endif; ?>
            <?php if ($password_error): ?>
                <div class="alert-academy danger" role="alert"><span>⚠</span><span><?php echo htmlspecialchars($password_error); ?></span></div>
            <?php endif; ?>

            <form method="POST" action="profile.php" data-guard novalidate>
                <div class="field">
                    <label for="current_password">Current password</label>
                    <div class="password-wrap">
                        <input class="form-control" type="password" id="current_password" name="current_password" required>
                        <button type="button" class="toggle-pass" data-target="current_password">Show</button>
                    </div>
                </div>
                <div class="field-row">
                    <div class="field">
                        <label for="new_password">New password</label>
                        <div class="password-wrap">
                            <input class="form-control" type="password" id="password" name="new_password" placeholder="At least 6 characters" required>
                            <button type="button" class="toggle-pass" data-target="password">Show</button>
                        </div>
                    </div>
                    <div class="field">
                        <label for="confirm_password">Confirm new password</label>
                        <div class="password-wrap">
                            <input class="form-control" type="password" id="confirm_password" name="confirm_password" required>
                            <button type="button" class="toggle-pass" data-target="confirm_password">Show</button>
                        </div>
                    </div>
                </div>
                <p class="hint" id="match-hint">&nbsp;</p>

                <div class="admin-form-actions">
                    <button type="submit" name="change_password" value="1" class="btn-academy" data-loading-text="Updating...">Change Password</button>
                </div>
            </form>
        </div>
    </main>
</div>

<script src="js/main.js"></script>
</body>
</html>
