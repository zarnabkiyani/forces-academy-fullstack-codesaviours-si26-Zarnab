<?php
require_once 'includes/auth.php';
require_admin_login();
require_once '../config/db.php';

$active_page = 'dashboard';
$page_title  = 'Dashboard';

function count_rows($conn, $table) {
    $count = 0;
    if ($result = mysqli_query($conn, "SELECT COUNT(*) AS c FROM {$table}")) {
        $count = (int) mysqli_fetch_assoc($result)['c'];
    }
    return $count;
}

$total_students    = count_rows($conn, 'students');
$total_courses     = count_rows($conn, 'courses');
$total_assignments = count_rows($conn, 'assignments');
$total_notices     = count_rows($conn, 'notices');

// A quick "recent activity" glance — latest 3 notices
$recent_notices = [];
if ($result = mysqli_query($conn, "SELECT title, posted_by, created_at FROM notices ORDER BY created_at DESC LIMIT 3")) {
    while ($row = mysqli_fetch_assoc($result)) {
        $recent_notices[] = $row;
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
        <div class="admin-meta">
            Signed in as <strong><?php echo htmlspecialchars($_SESSION['admin_username']); ?></strong>
        </div>

        <div class="welcome-banner">
            <p class="eyebrow">Admin Overview</p>
            <h2>Welcome back, <?php echo htmlspecialchars($_SESSION['admin_username']); ?>.</h2>
            <p>Here's the current state of the academy at a glance.</p>
        </div>

        <div class="stat-grid">
            <div class="stat-card">
                <div class="stat-label">Total students</div>
                <div class="stat-value"><?php echo $total_students; ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Total courses</div>
                <div class="stat-value"><?php echo $total_courses; ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Total assignments</div>
                <div class="stat-value"><?php echo $total_assignments; ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Total notices</div>
                <div class="stat-value"><?php echo $total_notices; ?></div>
            </div>
        </div>

        <div class="quick-links">
            <a href="students.php" class="quick-link-btn">Manage students →</a>
            <a href="courses.php" class="quick-link-btn ghost">Manage courses →</a>
            <a href="results.php" class="quick-link-btn ghost">Upload results →</a>
            <a href="notices.php" class="quick-link-btn ghost">Post a notice →</a>
        </div>

        <div class="panel">
            <h3>Recent notices</h3>
            <?php if (empty($recent_notices)): ?>
                <p class="muted">No notices posted yet. Use "Post Notice" to add one.</p>
            <?php else: ?>
                <?php foreach ($recent_notices as $n): ?>
                    <div class="notice-row">
                        <div class="notice-title"><?php echo htmlspecialchars($n['title']); ?></div>
                        <div class="notice-meta">
                            Posted by <?php echo htmlspecialchars($n['posted_by'] ?: 'Academy Staff'); ?> ·
                            <?php echo htmlspecialchars(date('d M Y, g:i A', strtotime($n['created_at']))); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>
</div>

<script src="../js/main.js"></script>
</body>
</html>
