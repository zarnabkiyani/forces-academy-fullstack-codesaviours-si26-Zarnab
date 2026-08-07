<?php
session_start();

if (!isset($_SESSION['student_id'])) {
    header('Location: login.php');
    exit;
}

require_once 'config/db.php';

$student_id = (int) $_SESSION['student_id'];

// -----------------------------------------------------------------
// Handle assignment submission (Post/Redirect/Get pattern)
// -----------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_assignment'])) {
    $assignment_id = (int) ($_POST['assignment_id'] ?? 0);

    // Confirm the assignment actually exists
    $check = mysqli_prepare($conn, "SELECT id FROM assignments WHERE id = ?");
    mysqli_stmt_bind_param($check, 'i', $assignment_id);
    mysqli_stmt_execute($check);
    $assignment_exists = mysqli_stmt_get_result($check)->fetch_assoc();
    mysqli_stmt_close($check);

    // Has this student already submitted this assignment?
    $dup = mysqli_prepare($conn, "SELECT id FROM submissions WHERE assignment_id = ? AND student_id = ?");
    mysqli_stmt_bind_param($dup, 'ii', $assignment_id, $student_id);
    mysqli_stmt_execute($dup);
    $already_submitted = mysqli_stmt_get_result($dup)->fetch_assoc();
    mysqli_stmt_close($dup);

    if (!$assignment_id || !$assignment_exists) {
        $_SESSION['flash_error'] = 'That assignment could not be found.';
    } elseif ($already_submitted) {
        $_SESSION['flash_error'] = 'You have already submitted this assignment.';
    } elseif (!isset($_FILES['assignment_file']) || $_FILES['assignment_file']['error'] !== UPLOAD_ERR_OK) {
        $_SESSION['flash_error'] = 'Please choose a file to upload.';
    } else {
        $file = $_FILES['assignment_file'];

        $allowed_ext  = ['pdf', 'jpg', 'jpeg', 'png', 'gif'];
        $allowed_mime = ['application/pdf', 'image/jpeg', 'image/png', 'image/gif'];
        $max_size     = 5 * 1024 * 1024; // 5 MB

        $ext  = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $mime = function_exists('mime_content_type') ? mime_content_type($file['tmp_name']) : $file['type'];

        if (!in_array($ext, $allowed_ext, true) || !in_array($mime, $allowed_mime, true)) {
            $_SESSION['flash_error'] = 'Only PDF and image files (jpg, png, gif) are allowed.';
        } elseif ($file['size'] > $max_size) {
            $_SESSION['flash_error'] = 'File is too large. Maximum size is 5 MB.';
        } else {
            $upload_dir = __DIR__ . '/uploads/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }

            // Unique filename so submissions never collide/overwrite each other
            $unique_name = 'sub_' . $student_id . '_' . $assignment_id . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
            $dest        = $upload_dir . $unique_name;

            if (move_uploaded_file($file['tmp_name'], $dest)) {
                $rel_path = 'uploads/' . $unique_name;

                $ins = mysqli_prepare($conn, "INSERT INTO submissions (assignment_id, student_id, file_path, status) VALUES (?, ?, ?, 'submitted')");
                mysqli_stmt_bind_param($ins, 'iis', $assignment_id, $student_id, $rel_path);

                if (mysqli_stmt_execute($ins)) {
                    $_SESSION['flash_success'] = 'Assignment submitted successfully!';
                } else {
                    $_SESSION['flash_error'] = 'Could not save your submission. Please try again.';
                }
                mysqli_stmt_close($ins);
            } else {
                $_SESSION['flash_error'] = 'Something went wrong while uploading. Please try again.';
            }
        }
    }

    header('Location: assignments.php');
    exit;
}

// -----------------------------------------------------------------
// Flash messages (Post/Redirect/Get)
// -----------------------------------------------------------------
$flash_success = $_SESSION['flash_success'] ?? null;
$flash_error   = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_success'], $_SESSION['flash_error']);

// -----------------------------------------------------------------
// Pull all assignments with their course name
// -----------------------------------------------------------------
$assignments = [];
$sql = "SELECT a.id, a.title, a.description, a.due_date, c.course_name
        FROM assignments a
        LEFT JOIN courses c ON a.course_id = c.id
        ORDER BY a.due_date ASC";
if ($result = mysqli_query($conn, $sql)) {
    while ($row = mysqli_fetch_assoc($result)) {
        $assignments[] = $row;
    }
}

// -----------------------------------------------------------------
// Which assignments has THIS student already submitted?
// -----------------------------------------------------------------
$submitted_ids = [];
$sub_stmt = mysqli_prepare($conn, "SELECT assignment_id FROM submissions WHERE student_id = ?");
mysqli_stmt_bind_param($sub_stmt, 'i', $student_id);
mysqli_stmt_execute($sub_stmt);
$sub_result = mysqli_stmt_get_result($sub_stmt);
while ($row = mysqli_fetch_assoc($sub_result)) {
    $submitted_ids[(int) $row['assignment_id']] = true;
}
mysqli_stmt_close($sub_stmt);

$active_page   = 'assignments';
$page_title    = 'Assignments';
$use_bootstrap = true; // needed for badge styling per Week 3 spec
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
                <p class="eyebrow">Coursework</p>
                <h1>Assignments</h1>
                <p class="lede">Submit your work before the due date. PDF and image files only.</p>
            </div>
        </div>

        <?php if ($flash_success): ?>
            <div class="alert-academy success" role="status">
                <span>✓</span><span><?php echo htmlspecialchars($flash_success); ?></span>
            </div>
        <?php endif; ?>

        <?php if ($flash_error): ?>
            <div class="alert-academy danger" role="alert">
                <span>⚠</span><span><?php echo htmlspecialchars($flash_error); ?></span>
            </div>
        <?php endif; ?>

        <?php if (empty($assignments)): ?>
            <div class="empty-state">
                <div class="empty-icon">📝</div>
                <h3>No assignments yet</h3>
                <p>Once an assignment is posted by the academy, it will appear here.</p>
            </div>
        <?php else: ?>
            <div class="assignment-grid">
                <?php foreach ($assignments as $a):
                    $is_submitted = isset($submitted_ids[(int) $a['id']]);
                    $due          = date('M j, Y', strtotime($a['due_date']));
                    $is_overdue   = !$is_submitted && strtotime($a['due_date']) < strtotime('today');
                ?>
                    <div class="assignment-card">
                        <div class="assignment-card-top">
                            <span class="badge bg-secondary"><?php echo htmlspecialchars($a['course_name'] ?: 'General'); ?></span>
                            <?php if ($is_submitted): ?>
                                <span class="badge bg-success">Submitted</span>
                            <?php elseif ($is_overdue): ?>
                                <span class="badge bg-danger">Overdue</span>
                            <?php endif; ?>
                        </div>

                        <h3><?php echo htmlspecialchars($a['title']); ?></h3>
                        <p class="assignment-desc"><?php echo htmlspecialchars($a['description'] ?: 'No description provided.'); ?></p>
                        <p class="assignment-due">Due <strong><?php echo htmlspecialchars($due); ?></strong></p>

                        <?php if ($is_submitted): ?>
                            <button type="button" class="btn-academy btn-sm" disabled>Submitted</button>
                        <?php else: ?>
                            <button type="button" class="btn-academy btn-sm"
                                    onclick="document.getElementById('upload-form-<?php echo (int) $a['id']; ?>').classList.toggle('open')">
                                Submit Assignment
                            </button>

                            <form id="upload-form-<?php echo (int) $a['id']; ?>" class="upload-form" method="POST" action="assignments.php" enctype="multipart/form-data">
                                <input type="hidden" name="assignment_id" value="<?php echo (int) $a['id']; ?>">
                                <input type="file" name="assignment_file" accept=".pdf,.jpg,.jpeg,.png,.gif" required>
                                <button type="submit" name="submit_assignment" value="1" class="btn-academy btn-sm">Upload</button>
                            </form>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>
</div>

<script src="js/main.js"></script>
</body>
</html>
