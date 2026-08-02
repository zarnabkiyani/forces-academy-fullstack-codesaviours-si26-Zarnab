<?php
require_once 'includes/auth.php';
require_admin_login();
require_once '../config/db.php';

// -----------------------------------------------------------------
// Handle add / update / delete (Post/Redirect/Get pattern)
// -----------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (isset($_POST['save_assignment'])) {
        $assignment_id = (int) ($_POST['assignment_id'] ?? 0);
        $title         = trim($_POST['title'] ?? '');
        $description   = trim($_POST['description'] ?? '');
        $course_id     = (int) ($_POST['course_id'] ?? 0);
        $due_date      = trim($_POST['due_date'] ?? '');

        if ($title === '' || !$course_id || $due_date === '') {
            $_SESSION['flash_error'] = 'Title, course, and due date are required.';
        } elseif ($assignment_id > 0) {
            $stmt = mysqli_prepare($conn, "UPDATE assignments SET title = ?, description = ?, course_id = ?, due_date = ? WHERE id = ?");
            mysqli_stmt_bind_param($stmt, 'ssisi', $title, $description, $course_id, $due_date, $assignment_id);
            if (mysqli_stmt_execute($stmt)) {
                $_SESSION['flash_success'] = 'Assignment updated successfully.';
            } else {
                $_SESSION['flash_error'] = 'Could not update the assignment.';
            }
            mysqli_stmt_close($stmt);
        } else {
            $stmt = mysqli_prepare($conn, "INSERT INTO assignments (title, description, course_id, due_date) VALUES (?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt, 'ssis', $title, $description, $course_id, $due_date);
            if (mysqli_stmt_execute($stmt)) {
                $_SESSION['flash_success'] = 'Assignment added successfully.';
            } else {
                $_SESSION['flash_error'] = 'Could not add the assignment.';
            }
            mysqli_stmt_close($stmt);
        }

        header('Location: assignments.php');
        exit;
    }

    if (isset($_POST['delete_assignment'])) {
        $assignment_id = (int) ($_POST['assignment_id'] ?? 0);
        if ($assignment_id > 0) {
            $stmt = mysqli_prepare($conn, "DELETE FROM assignments WHERE id = ?");
            mysqli_stmt_bind_param($stmt, 'i', $assignment_id);
            if (mysqli_stmt_execute($stmt)) {
                $_SESSION['flash_success'] = 'Assignment deleted.';
            } else {
                $_SESSION['flash_error'] = 'Could not delete the assignment.';
            }
            mysqli_stmt_close($stmt);
        }
        header('Location: assignments.php');
        exit;
    }
}

$flash_success = $_SESSION['flash_success'] ?? null;
$flash_error   = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_success'], $_SESSION['flash_error']);

// Editing?
$editing = null;
$edit_id = (int) ($_GET['edit'] ?? 0);
if ($edit_id > 0) {
    $stmt = mysqli_prepare($conn, "SELECT id, title, description, course_id, due_date FROM assignments WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $edit_id);
    mysqli_stmt_execute($stmt);
    $editing = mysqli_stmt_get_result($stmt)->fetch_assoc();
    mysqli_stmt_close($stmt);
}

$courses = [];
if ($result = mysqli_query($conn, "SELECT id, course_name FROM courses ORDER BY course_name ASC")) {
    while ($row = mysqli_fetch_assoc($result)) { $courses[] = $row; }
}

$assignments = [];
$sql = "SELECT a.id, a.title, a.description, a.due_date, c.course_name,
               (SELECT COUNT(*) FROM submissions s WHERE s.assignment_id = a.id) AS submission_count
        FROM assignments a
        LEFT JOIN courses c ON a.course_id = c.id
        ORDER BY a.due_date ASC";
if ($result = mysqli_query($conn, $sql)) {
    while ($row = mysqli_fetch_assoc($result)) { $assignments[] = $row; }
}

$active_page   = 'assignments';
$page_title    = 'Manage Assignments';
$use_bootstrap = true;
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
                <h1>Manage Assignments</h1>
                <p class="lede">Post new assignments, or edit and remove existing ones.</p>
            </div>
        </div>

        <?php if ($flash_success): ?>
            <div class="alert-academy success" role="status"><span>✓</span><span><?php echo htmlspecialchars($flash_success); ?></span></div>
        <?php endif; ?>
        <?php if ($flash_error): ?>
            <div class="alert-academy danger" role="alert"><span>⚠</span><span><?php echo htmlspecialchars($flash_error); ?></span></div>
        <?php endif; ?>

        <?php if (empty($courses)): ?>
            <div class="empty-state">
                <div class="empty-icon">📚</div>
                <h3>Add a course first</h3>
                <p>Assignments belong to a course — add one from "Manage Courses" first.</p>
            </div>
        <?php else: ?>
            <div class="panel">
                <h3><?php echo $editing ? 'Edit Assignment' : 'New Assignment'; ?></h3>
                <form method="POST" action="assignments.php" data-guard novalidate>
                    <?php if ($editing): ?>
                        <input type="hidden" name="assignment_id" value="<?php echo (int) $editing['id']; ?>">
                    <?php endif; ?>
                    <div class="admin-form-grid">
                        <div class="field">
                            <label for="title">Title</label>
                            <input class="form-control" type="text" id="title" name="title" placeholder="e.g. Build a Login Form"
                                   value="<?php echo htmlspecialchars($editing['title'] ?? ''); ?>" required>
                        </div>
                        <div class="field">
                            <label for="course_id">Course</label>
                            <select class="form-control" id="course_id" name="course_id" required>
                                <option value="">Select a course…</option>
                                <?php foreach ($courses as $c): ?>
                                    <option value="<?php echo (int) $c['id']; ?>" <?php echo (isset($editing['course_id']) && (int) $editing['course_id'] === (int) $c['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($c['course_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="field">
                            <label for="due_date">Due date</label>
                            <input class="form-control" type="date" id="due_date" name="due_date"
                                   value="<?php echo htmlspecialchars($editing['due_date'] ?? ''); ?>" required>
                        </div>
                        <div class="field full">
                            <label for="description">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3" placeholder="What should students do?"><?php echo htmlspecialchars($editing['description'] ?? ''); ?></textarea>
                        </div>
                    </div>

                    <div class="admin-form-actions">
                        <button type="submit" name="save_assignment" value="1" class="btn-academy" data-loading-text="Saving...">
                            <?php echo $editing ? 'Update Assignment' : 'Post Assignment'; ?>
                        </button>
                        <?php if ($editing): ?>
                            <a href="assignments.php" class="cancel-link">Cancel edit</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        <?php endif; ?>

        <div class="panel">
            <div class="panel-head">
                <h3>All Assignments</h3>
                <span class="count-tag"><?php echo count($assignments); ?> total</span>
            </div>

            <?php if (empty($assignments)): ?>
                <p class="muted">No assignments posted yet.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle mb-0">
                        <thead>
                            <tr><th>Title</th><th>Course</th><th>Due Date</th><th>Submissions</th><th>Actions</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($assignments as $a): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($a['title']); ?></td>
                                    <td><?php echo htmlspecialchars($a['course_name'] ?: '—'); ?></td>
                                    <td><?php echo htmlspecialchars(date('d M Y', strtotime($a['due_date']))); ?></td>
                                    <td><span class="badge bg-secondary"><?php echo (int) $a['submission_count']; ?></span></td>
                                    <td>
                                        <div class="action-btns">
                                            <a class="btn-action" href="assignments.php?edit=<?php echo (int) $a['id']; ?>">Edit</a>
                                            <button type="button" class="btn-action danger" data-bs-toggle="modal" data-bs-target="#deleteAssignment<?php echo (int) $a['id']; ?>">Delete</button>
                                        </div>
                                    </td>
                                </tr>

                                <div class="modal fade" id="deleteAssignment<?php echo (int) $a['id']; ?>" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Delete assignment?</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <p>Delete "<strong><?php echo htmlspecialchars($a['title']); ?></strong>"?</p>
                                                <p class="text-danger-academy">Any student submissions for it will be deleted too. This cannot be undone.</p>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn-action" data-bs-dismiss="modal">Cancel</button>
                                                <form method="POST" action="assignments.php" style="margin:0;">
                                                    <input type="hidden" name="assignment_id" value="<?php echo (int) $a['id']; ?>">
                                                    <button type="submit" name="delete_assignment" value="1" class="btn-action danger">Yes, delete</button>
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
