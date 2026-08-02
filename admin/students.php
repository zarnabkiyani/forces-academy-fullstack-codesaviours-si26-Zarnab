<?php
require_once 'includes/auth.php';
require_admin_login();
require_once '../config/db.php';

// -----------------------------------------------------------------
// Handle delete (Post/Redirect/Get pattern)
// -----------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_student'])) {
    $student_id = (int) ($_POST['student_id'] ?? 0);

    if ($student_id > 0) {
        $stmt = mysqli_prepare($conn, "DELETE FROM students WHERE id = ?");
        mysqli_stmt_bind_param($stmt, 'i', $student_id);
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['flash_success'] = 'Student removed successfully.';
        } else {
            $_SESSION['flash_error'] = 'Could not delete this student. Please try again.';
        }
        mysqli_stmt_close($stmt);
    }

    header('Location: students.php');
    exit;
}

$flash_success = $_SESSION['flash_success'] ?? null;
$flash_error   = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_success'], $_SESSION['flash_error']);

// -----------------------------------------------------------------
// Search (by name or roll number)
// -----------------------------------------------------------------
$search = trim($_GET['q'] ?? '');

if ($search !== '') {
    $like = '%' . $search . '%';
    $sql  = "SELECT id, full_name, email, roll_number, class, created_at
             FROM students
             WHERE full_name LIKE ? OR roll_number LIKE ?
             ORDER BY created_at DESC";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'ss', $like, $like);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
} else {
    $result = mysqli_query($conn, "SELECT id, full_name, email, roll_number, class, created_at FROM students ORDER BY created_at DESC");
}

$students = [];
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $students[] = $row;
    }
}

$active_page   = 'students';
$page_title    = 'Manage Students';
$use_bootstrap = true; // needed for the delete-confirmation modal
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
                <p class="eyebrow">Student Records</p>
                <h1>Manage Students</h1>
                <p class="lede">Search, review, and remove student accounts.</p>
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

        <form method="GET" action="students.php" class="search-form">
            <input class="form-control" type="text" name="q" placeholder="Search by name or roll number..." value="<?php echo htmlspecialchars($search); ?>">
            <button type="submit">Search</button>
            <?php if ($search !== ''): ?>
                <a class="clear-link" href="students.php">Clear</a>
            <?php endif; ?>
        </form>

        <div class="panel">
            <div class="panel-head">
                <h3>All Students</h3>
                <span class="count-tag"><?php echo count($students); ?> total</span>
            </div>

            <?php if (empty($students)): ?>
                <div class="empty-state">
                    <div class="empty-icon">🎓</div>
                    <h3>No students found</h3>
                    <p><?php echo $search !== '' ? 'No students match that search.' : 'No students have registered yet.'; ?></p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Roll Number</th>
                                <th>Class</th>
                                <th>Registered</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($students as $s): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($s['full_name']); ?></td>
                                    <td><?php echo htmlspecialchars($s['email']); ?></td>
                                    <td class="mono"><?php echo htmlspecialchars($s['roll_number']); ?></td>
                                    <td><?php echo htmlspecialchars($s['class']); ?></td>
                                    <td><?php echo htmlspecialchars(date('d M Y', strtotime($s['created_at']))); ?></td>
                                    <td>
                                        <div class="action-btns">
                                            <a class="btn-action" href="student_view.php?id=<?php echo (int) $s['id']; ?>">View</a>
                                            <button type="button" class="btn-action danger" data-bs-toggle="modal" data-bs-target="#deleteModal<?php echo (int) $s['id']; ?>">Delete</button>
                                        </div>
                                    </td>
                                </tr>

                                <!-- Confirmation modal for this student -->
                                <div class="modal fade" id="deleteModal<?php echo (int) $s['id']; ?>" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Remove student?</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <p>You're about to permanently remove <strong><?php echo htmlspecialchars($s['full_name']); ?></strong> (Roll #<?php echo htmlspecialchars($s['roll_number']); ?>).</p>
                                                <p class="text-danger-academy">This also deletes their submissions and results. This cannot be undone.</p>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn-action" data-bs-dismiss="modal">Cancel</button>
                                                <form method="POST" action="students.php" style="margin:0;">
                                                    <input type="hidden" name="student_id" value="<?php echo (int) $s['id']; ?>">
                                                    <button type="submit" name="delete_student" value="1" class="btn-action danger">Yes, delete</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="../js/main.js"></script>
</body>
</html>
