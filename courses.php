<?php
session_start();

if (!isset($_SESSION['student_id'])) {
    header('Location: login.php');
    exit;
}

require_once 'config/db.php';

$active_page = 'courses';
$page_title  = 'My Courses';

$courses = [];
if ($result = mysqli_query($conn, "SELECT course_name, description, teacher_name FROM courses ORDER BY created_at DESC")) {
    while ($row = mysqli_fetch_assoc($result)) {
        $courses[] = $row;
    }
}
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
                <h1>My Courses</h1>
                <p class="lede">All courses currently offered at the academy.</p>
            </div>
        </div>

        <?php if (empty($courses)): ?>
            <div class="empty-state">
                <div class="empty-icon">📚</div>
                <h3>No courses yet</h3>
                <p>Once courses are added by the academy, they'll appear here.</p>
            </div>
        <?php else: ?>
            <div class="course-grid">
                <?php foreach ($courses as $c): ?>
                    <div class="course-card">
                        <div class="course-badge"><?php echo htmlspecialchars(strtoupper(substr($c['course_name'], 0, 1))); ?></div>
                        <h3><?php echo htmlspecialchars($c['course_name']); ?></h3>
                        <p class="course-desc"><?php echo htmlspecialchars($c['description'] ?: 'No description provided yet.'); ?></p>
                        <p class="course-teacher">Taught by <strong><?php echo htmlspecialchars($c['teacher_name'] ?: 'To be announced'); ?></strong></p>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>
</div>

<script src="js/main.js"></script>
</body>
</html>
