<?php
require_once 'includes/auth.php';
require_admin_login();
require_once '../config/db.php';

$student_id = (int) ($_GET['id'] ?? 0);

$stmt = mysqli_prepare($conn, "SELECT id, full_name, email, roll_number, class, created_at FROM students WHERE id = ?");
mysqli_stmt_bind_param($stmt, 'i', $student_id);
mysqli_stmt_execute($stmt);
$student = mysqli_stmt_get_result($stmt)->fetch_assoc();
mysqli_stmt_close($stmt);

if (!$student) {
    header('Location: students.php');
    exit;
}

// This student's results
$results = [];
$stmt = mysqli_prepare($conn, "SELECT r.subject, r.marks, r.total_marks, r.grade, r.exam_type, c.course_name
                                FROM results r
                                LEFT JOIN courses c ON r.course_id = c.id
                                WHERE r.student_id = ?
                                ORDER BY r.created_at DESC");
mysqli_stmt_bind_param($stmt, 'i', $student_id);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
while ($row = mysqli_fetch_assoc($res)) { $results[] = $row; }
mysqli_stmt_close($stmt);

// This student's assignment submissions
$submissions = [];
$stmt = mysqli_prepare($conn, "SELECT a.title, s.submitted_at, s.status
                                FROM submissions s
                                JOIN assignments a ON s.assignment_id = a.id
                                WHERE s.student_id = ?
                                ORDER BY s.submitted_at DESC");
mysqli_stmt_bind_param($stmt, 'i', $student_id);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
while ($row = mysqli_fetch_assoc($res)) { $submissions[] = $row; }
mysqli_stmt_close($stmt);

$active_page   = 'students';
$page_title    = 'Student Details';
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
                <p class="eyebrow">Student Profile</p>
                <h1><?php echo htmlspecialchars($student['full_name']); ?></h1>
                <p class="lede">Roll #<?php echo htmlspecialchars($student['roll_number']); ?> · <?php echo htmlspecialchars($student['class']); ?></p>
            </div>
            <a href="students.php" class="quick-link-btn ghost">← Back to all students</a>
        </div>

        <div class="panel">
            <h3>Profile</h3>
            <div class="admin-form-grid">
                <div class="field"><label>Full name</label><p><?php echo htmlspecialchars($student['full_name']); ?></p></div>
                <div class="field"><label>Email</label><p><?php echo htmlspecialchars($student['email']); ?></p></div>
                <div class="field"><label>Roll number</label><p class="mono"><?php echo htmlspecialchars($student['roll_number']); ?></p></div>
                <div class="field"><label>Class</label><p><?php echo htmlspecialchars($student['class']); ?></p></div>
                <div class="field"><label>Registered on</label><p><?php echo htmlspecialchars(date('d M Y, g:i A', strtotime($student['created_at']))); ?></p></div>
            </div>
        </div>

        <div class="panel">
            <div class="panel-head">
                <h3>Results</h3>
                <span class="count-tag"><?php echo count($results); ?> entries</span>
            </div>
            <?php if (empty($results)): ?>
                <p class="muted">No results uploaded for this student yet.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped align-middle mb-0">
                        <thead><tr><th>Course</th><th>Subject</th><th>Marks</th><th>Grade</th><th>Exam Type</th></tr></thead>
                        <tbody>
                        <?php foreach ($results as $r): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($r['course_name'] ?: '—'); ?></td>
                                <td><?php echo htmlspecialchars($r['subject']); ?></td>
                                <td><?php echo (int) $r['marks']; ?> / <?php echo (int) $r['total_marks']; ?></td>
                                <td><span class="badge bg-primary"><?php echo htmlspecialchars($r['grade']); ?></span></td>
                                <td><?php echo htmlspecialchars($r['exam_type']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <div class="panel">
            <div class="panel-head">
                <h3>Assignment Submissions</h3>
                <span class="count-tag"><?php echo count($submissions); ?> entries</span>
            </div>
            <?php if (empty($submissions)): ?>
                <p class="muted">This student hasn't submitted any assignments yet.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped align-middle mb-0">
                        <thead><tr><th>Assignment</th><th>Submitted</th><th>Status</th></tr></thead>
                        <tbody>
                        <?php foreach ($submissions as $sub): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($sub['title']); ?></td>
                                <td><?php echo htmlspecialchars(date('d M Y, g:i A', strtotime($sub['submitted_at']))); ?></td>
                                <td><span class="badge <?php echo $sub['status'] === 'graded' ? 'bg-success' : 'bg-secondary'; ?>"><?php echo htmlspecialchars(ucfirst($sub['status'])); ?></span></td>
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
