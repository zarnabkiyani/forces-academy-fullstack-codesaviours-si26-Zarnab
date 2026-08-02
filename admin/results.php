<?php
require_once 'includes/auth.php';
require_admin_login();
require_once '../config/db.php';

// -----------------------------------------------------------------
// Handle upload (Post/Redirect/Get pattern)
// -----------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_result'])) {
    $student_id  = (int) ($_POST['student_id'] ?? 0);
    $course_id   = (int) ($_POST['course_id'] ?? 0);
    $subject     = trim($_POST['subject'] ?? '');
    $marks       = (int) ($_POST['marks'] ?? -1);
    $total_marks = (int) ($_POST['total_marks'] ?? 0);
    $grade       = trim($_POST['grade'] ?? '');
    $exam_type   = trim($_POST['exam_type'] ?? '');

    if (!$student_id || !$course_id || $subject === '' || $grade === '' || $exam_type === '') {
        $_SESSION['flash_error'] = 'Please fill in every field.';
    } elseif ($total_marks <= 0 || $marks < 0 || $marks > $total_marks) {
        $_SESSION['flash_error'] = 'Marks must be between 0 and the total marks.';
    } else {
        $stmt = mysqli_prepare($conn, "INSERT INTO results (student_id, course_id, subject, marks, total_marks, grade, exam_type) VALUES (?, ?, ?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, 'iisiiss', $student_id, $course_id, $subject, $marks, $total_marks, $grade, $exam_type);
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['flash_success'] = 'Result uploaded successfully.';
        } else {
            $_SESSION['flash_error'] = 'Could not save the result. Please try again.';
        }
        mysqli_stmt_close($stmt);
    }

    header('Location: results.php');
    exit;
}

$flash_success = $_SESSION['flash_success'] ?? null;
$flash_error   = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_success'], $_SESSION['flash_error']);

// Dropdown data
$students = [];
if ($result = mysqli_query($conn, "SELECT id, full_name, roll_number FROM students ORDER BY full_name ASC")) {
    while ($row = mysqli_fetch_assoc($result)) { $students[] = $row; }
}
$courses = [];
if ($result = mysqli_query($conn, "SELECT id, course_name FROM courses ORDER BY course_name ASC")) {
    while ($row = mysqli_fetch_assoc($result)) { $courses[] = $row; }
}

// Recently uploaded results
$recent_results = [];
$sql = "SELECT r.subject, r.marks, r.total_marks, r.grade, r.exam_type, r.created_at,
               s.full_name AS student_name, s.roll_number, c.course_name
        FROM results r
        JOIN students s ON r.student_id = s.id
        LEFT JOIN courses c ON r.course_id = c.id
        ORDER BY r.created_at DESC
        LIMIT 15";
if ($result = mysqli_query($conn, $sql)) {
    while ($row = mysqli_fetch_assoc($result)) { $recent_results[] = $row; }
}

function grade_badge_class($grade) {
    $g = strtoupper(trim($grade));
    if (strpos($g, 'A') === 0) return 'bg-success';
    if (strpos($g, 'F') === 0) return 'bg-danger';
    if (strpos($g, 'B') === 0 || strpos($g, 'C') === 0) return 'bg-primary';
    return 'bg-secondary';
}

$active_page   = 'results';
$page_title    = 'Upload Results';
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
                <p class="eyebrow">Academic Records</p>
                <h1>Upload Results</h1>
                <p class="lede">Enter a student's marks for a course and exam.</p>
            </div>
        </div>

        <?php if ($flash_success): ?>
            <div class="alert-academy success" role="status"><span>✓</span><span><?php echo htmlspecialchars($flash_success); ?></span></div>
        <?php endif; ?>
        <?php if ($flash_error): ?>
            <div class="alert-academy danger" role="alert"><span>⚠</span><span><?php echo htmlspecialchars($flash_error); ?></span></div>
        <?php endif; ?>

        <?php if (empty($students)): ?>
            <div class="empty-state">
                <div class="empty-icon">🎓</div>
                <h3>No students registered yet</h3>
                <p>Results can be uploaded once at least one student has registered.</p>
            </div>
        <?php elseif (empty($courses)): ?>
            <div class="empty-state">
                <div class="empty-icon">📚</div>
                <h3>No courses yet</h3>
                <p>Add a course first from "Manage Courses" before uploading results.</p>
            </div>
        <?php else: ?>
            <div class="panel">
                <h3>New Result</h3>
                <form method="POST" action="results.php" data-guard novalidate>
                    <div class="admin-form-grid">
                        <div class="field">
                            <label for="student_id">Student</label>
                            <select class="form-control" id="student_id" name="student_id" required>
                                <option value="">Select a student…</option>
                                <?php foreach ($students as $s): ?>
                                    <option value="<?php echo (int) $s['id']; ?>">
                                        <?php echo htmlspecialchars($s['full_name']); ?> (#<?php echo htmlspecialchars($s['roll_number']); ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="field">
                            <label for="course_id">Course</label>
                            <select class="form-control" id="course_id" name="course_id" required>
                                <option value="">Select a course…</option>
                                <?php foreach ($courses as $c): ?>
                                    <option value="<?php echo (int) $c['id']; ?>"><?php echo htmlspecialchars($c['course_name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="field">
                            <label for="subject">Subject</label>
                            <input class="form-control" type="text" id="subject" name="subject" placeholder="e.g. PHP & MySQL" required>
                        </div>
                        <div class="field">
                            <label for="exam_type">Exam type</label>
                            <input class="form-control" type="text" id="exam_type" name="exam_type" placeholder="e.g. Midterm, Final, Quiz" required>
                        </div>
                        <div class="field">
                            <label for="marks">Marks obtained</label>
                            <input class="form-control" type="number" id="marks" name="marks" min="0" required>
                        </div>
                        <div class="field">
                            <label for="total_marks">Total marks</label>
                            <input class="form-control" type="number" id="total_marks" name="total_marks" min="1" required>
                        </div>
                        <div class="field">
                            <label for="grade">Grade</label>
                            <input class="form-control" type="text" id="grade" name="grade" placeholder="e.g. A, B+, C" maxlength="5" required>
                        </div>
                    </div>

                    <div class="admin-form-actions">
                        <button type="submit" name="upload_result" value="1" class="btn-academy" data-loading-text="Saving...">Upload Result</button>
                    </div>
                </form>
            </div>
        <?php endif; ?>

        <div class="panel">
            <div class="panel-head">
                <h3>Recently Uploaded Results</h3>
                <span class="count-tag"><?php echo count($recent_results); ?> shown</span>
            </div>

            <?php if (empty($recent_results)): ?>
                <p class="muted">No results have been uploaded yet.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle mb-0">
                        <thead>
                            <tr><th>Student</th><th>Course</th><th>Subject</th><th>Marks</th><th>Grade</th><th>Exam Type</th><th>Uploaded</th></tr>
                        </thead>
                        <tbody>
                        <?php foreach ($recent_results as $r): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($r['student_name']); ?> <span class="mono" style="color:var(--slate-light);font-size:0.8em;">#<?php echo htmlspecialchars($r['roll_number']); ?></span></td>
                                <td><?php echo htmlspecialchars($r['course_name'] ?: '—'); ?></td>
                                <td><?php echo htmlspecialchars($r['subject']); ?></td>
                                <td><?php echo (int) $r['marks']; ?> / <?php echo (int) $r['total_marks']; ?></td>
                                <td><span class="badge <?php echo grade_badge_class($r['grade']); ?>"><?php echo htmlspecialchars($r['grade']); ?></span></td>
                                <td><?php echo htmlspecialchars($r['exam_type']); ?></td>
                                <td><?php echo htmlspecialchars(date('d M Y', strtotime($r['created_at']))); ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </main>
</div>

<script src="../js/main.js"></script>
</body>
</html>
