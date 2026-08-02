<?php
session_start();

if (!isset($_SESSION['student_id'])) {
    header('Location: login.php');
    exit;
}

require_once 'config/db.php';

$student_id = (int) $_SESSION['student_id'];

// Pull results for the logged-in student only
$results = [];
$stmt = mysqli_prepare($conn, "SELECT subject, marks, total_marks, grade, exam_type FROM results WHERE student_id = ? ORDER BY exam_type, subject");
mysqli_stmt_bind_param($stmt, 'i', $student_id);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
while ($row = mysqli_fetch_assoc($res)) {
    $results[] = $row;
}
mysqli_stmt_close($stmt);

function grade_badge_class($grade) {
    $g = strtoupper(trim($grade));
    if (strpos($g, 'A') === 0) return 'bg-success';
    if (strpos($g, 'F') === 0) return 'bg-danger';
    if (strpos($g, 'B') === 0 || strpos($g, 'C') === 0) return 'bg-primary';
    return 'bg-secondary';
}

$active_page   = 'results';
$page_title    = 'My Results';
$use_bootstrap = true; // needed for Bootstrap table/badges per Week 3 spec
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
                <p class="eyebrow">Academic Record</p>
                <h1>My Results</h1>
                <p class="lede">Your marks across all subjects and exams, most recent first.</p>
            </div>
        </div>

        <?php if (empty($results)): ?>
            <div class="empty-state">
                <div class="empty-icon">📊</div>
                <h3>No results yet</h3>
                <p>Once your exam results are entered by the academy, they'll appear here.</p>
            </div>
        <?php else: ?>
            <div class="panel">
                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Subject</th>
                                <th>Marks Obtained</th>
                                <th>Total Marks</th>
                                <th>Grade</th>
                                <th>Exam Type</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($results as $r): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($r['subject']); ?></td>
                                    <td><?php echo (int) $r['marks']; ?></td>
                                    <td><?php echo (int) $r['total_marks']; ?></td>
                                    <td><span class="badge <?php echo grade_badge_class($r['grade']); ?>"><?php echo htmlspecialchars($r['grade']); ?></span></td>
                                    <td><?php echo htmlspecialchars($r['exam_type']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>
    </main>
</div>

<script src="js/main.js"></script>
</body>
</html>
