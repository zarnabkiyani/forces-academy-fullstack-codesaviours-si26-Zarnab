<?php
require_once 'includes/auth.php';
require_admin_login();
require_once '../config/db.php';

// -----------------------------------------------------------------
// Handle add / update / delete (Post/Redirect/Get pattern)
// -----------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (isset($_POST['save_course'])) {
        $course_id   = (int) ($_POST['course_id'] ?? 0);
        $course_name = trim($_POST['course_name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $teacher     = trim($_POST['teacher_name'] ?? '');

        if ($course_name === '') {
            $_SESSION['flash_error'] = 'Course name is required.';
        } elseif ($course_id > 0) {
            // Update existing course
            $stmt = mysqli_prepare($conn, "UPDATE courses SET course_name = ?, description = ?, teacher_name = ? WHERE id = ?");
            mysqli_stmt_bind_param($stmt, 'sssi', $course_name, $description, $teacher, $course_id);
            if (mysqli_stmt_execute($stmt)) {
                $_SESSION['flash_success'] = 'Course updated successfully.';
            } else {
                $_SESSION['flash_error'] = 'Could not update the course.';
            }
            mysqli_stmt_close($stmt);
        } else {
            // Insert new course
            $stmt = mysqli_prepare($conn, "INSERT INTO courses (course_name, description, teacher_name) VALUES (?, ?, ?)");
            mysqli_stmt_bind_param($stmt, 'sss', $course_name, $description, $teacher);
            if (mysqli_stmt_execute($stmt)) {
                $_SESSION['flash_success'] = 'Course added successfully.';
            } else {
                $_SESSION['flash_error'] = 'Could not add the course.';
            }
            mysqli_stmt_close($stmt);
        }

        header('Location: courses.php');
        exit;
    }

    if (isset($_POST['delete_course'])) {
        $course_id = (int) ($_POST['course_id'] ?? 0);
        if ($course_id > 0) {
            $stmt = mysqli_prepare($conn, "DELETE FROM courses WHERE id = ?");
            mysqli_stmt_bind_param($stmt, 'i', $course_id);
            if (mysqli_stmt_execute($stmt)) {
                $_SESSION['flash_success'] = 'Course deleted successfully.';
            } else {
                $_SESSION['flash_error'] = 'Could not delete the course.';
            }
            mysqli_stmt_close($stmt);
        }
        header('Location: courses.php');
        exit;
    }
}

$flash_success = $_SESSION['flash_success'] ?? null;
$flash_error   = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_success'], $_SESSION['flash_error']);

// -----------------------------------------------------------------
// Are we editing a course? Pre-fill the form if so.
// -----------------------------------------------------------------
$editing = null;
$edit_id = (int) ($_GET['edit'] ?? 0);
if ($edit_id > 0) {
    $stmt = mysqli_prepare($conn, "SELECT id, course_name, description, teacher_name FROM courses WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $edit_id);
    mysqli_stmt_execute($stmt);
    $editing = mysqli_stmt_get_result($stmt)->fetch_assoc();
    mysqli_stmt_close($stmt);
}

// -----------------------------------------------------------------
// All courses
// -----------------------------------------------------------------
$courses = [];
if ($result = mysqli_query($conn, "SELECT id, course_name, description, teacher_name, created_at FROM courses ORDER BY created_at DESC")) {
    while ($row = mysqli_fetch_assoc($result)) {
        $courses[] = $row;
    }
}

$active_page   = 'courses';
$page_title    = 'Manage Courses';
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
                <p class="eyebrow">Course Catalogue</p>
                <h1>Manage Courses</h1>
                <p class="lede">Add new courses or edit and remove existing ones.</p>
            </div>
        </div>

        <?php if ($flash_success): ?>
            <div class="alert-academy success" role="status"><span>✓</span><span><?php echo htmlspecialchars($flash_success); ?></span></div>
        <?php endif; ?>
        <?php if ($flash_error): ?>
            <div class="alert-academy danger" role="alert"><span>⚠</span><span><?php echo htmlspecialchars($flash_error); ?></span></div>
        <?php endif; ?>

        <div class="panel">
            <h3><?php echo $editing ? 'Edit Course' : 'Add New Course'; ?></h3>
            <form method="POST" action="courses.php" data-guard novalidate>
                <?php if ($editing): ?>
                    <input type="hidden" name="course_id" value="<?php echo (int) $editing['id']; ?>">
                <?php endif; ?>
                <div class="admin-form-grid">
                    <div class="field">
                        <label for="course_name">Course name</label>
                        <input class="form-control" type="text" id="course_name" name="course_name" placeholder="e.g. Full Stack Web Development"
                               value="<?php echo htmlspecialchars($editing['course_name'] ?? ''); ?>" required>
                    </div>
                    <div class="field">
                        <label for="teacher_name">Teacher name</label>
                        <input class="form-control" type="text" id="teacher_name" name="teacher_name" placeholder="e.g. Sir Ahmed Raza"
                               value="<?php echo htmlspecialchars($editing['teacher_name'] ?? ''); ?>">
                    </div>
                    <div class="field full">
                        <label for="description">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3" placeholder="What will students learn in this course?"><?php echo htmlspecialchars($editing['description'] ?? ''); ?></textarea>
                    </div>
                </div>

                <div class="admin-form-actions">
                    <button type="submit" name="save_course" value="1" class="btn-academy" data-loading-text="Saving...">
                        <?php echo $editing ? 'Update Course' : 'Add Course'; ?>
                    </button>
                    <?php if ($editing): ?>
                        <a href="courses.php" class="cancel-link">Cancel edit</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <div class="panel">
            <div class="panel-head">
                <h3>All Courses</h3>
                <span class="count-tag"><?php echo count($courses); ?> total</span>
            </div>

            <?php if (empty($courses)): ?>
                <div class="empty-state">
                    <div class="empty-icon">📚</div>
                    <h3>No courses yet</h3>
                    <p>Add your first course using the form above.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle mb-0">
                        <thead>
                            <tr><th>Course</th><th>Teacher</th><th>Description</th><th>Actions</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($courses as $c): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($c['course_name']); ?></td>
                                    <td><?php echo htmlspecialchars($c['teacher_name'] ?: '—'); ?></td>
                                    <td><?php echo htmlspecialchars(mb_strimwidth($c['description'] ?: '—', 0, 70, '…')); ?></td>
                                    <td>
                                        <div class="action-btns">
                                            <a class="btn-action" href="courses.php?edit=<?php echo (int) $c['id']; ?>">Edit</a>
                                            <button type="button" class="btn-action danger" data-bs-toggle="modal" data-bs-target="#deleteCourse<?php echo (int) $c['id']; ?>">Delete</button>
                                        </div>
                                    </td>
                                </tr>

                                <div class="modal fade" id="deleteCourse<?php echo (int) $c['id']; ?>" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Delete course?</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <p>You're about to permanently delete <strong><?php echo htmlspecialchars($c['course_name']); ?></strong>.</p>
                                                <p class="text-danger-academy">Its assignments will be deleted too. This cannot be undone.</p>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn-action" data-bs-dismiss="modal">Cancel</button>
                                                <form method="POST" action="courses.php" style="margin:0;">
                                                    <input type="hidden" name="course_id" value="<?php echo (int) $c['id']; ?>">
                                                    <button type="submit" name="delete_course" value="1" class="btn-action danger">Yes, delete</button>
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
