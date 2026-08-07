<?php
session_start();

if (!isset($_SESSION['student_id'])) {
    header('Location: login.php');
    exit;
}

require_once 'config/db.php';

$student_name = $_SESSION['student_name'];
$student_id   = $_SESSION['student_id'];
$active_page  = 'dashboard';
$page_title   = 'Dashboard';

// Total courses (real count from DB)
$course_count = 0;
if ($result = mysqli_query($conn, "SELECT COUNT(*) AS c FROM courses")) {
    $course_count = (int) mysqli_fetch_assoc($result)['c'];
}

// Pending assignments — placeholder until the Assignments module is built
$pending_assignments = 0;

// Latest notice title, for the stat card
$latest_notice_title = null;
if ($result = mysqli_query($conn, "SELECT title FROM notices ORDER BY created_at DESC LIMIT 1")) {
    if ($row = mysqli_fetch_assoc($result)) {
        $latest_notice_title = $row['title'];
    }
}

// Last 3 notices for the "Recent notices" section
$recent_notices = [];
if ($result = mysqli_query($conn, "SELECT title, content, posted_by, created_at FROM notices ORDER BY created_at DESC LIMIT 3")) {
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
        <div class="welcome-banner">
            <p class="eyebrow">Student Dashboard</p>
            <h2>Hello, <?php echo htmlspecialchars($student_name); ?>!</h2>
            <p>Here's what's happening across your courses and notices today.</p>
        </div>

        <div class="stat-grid">
            <div class="stat-card">
                <div class="stat-label">Total courses</div>
                <div class="stat-value"><?php echo $course_count; ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Pending assignments</div>
                <div class="stat-value"><?php echo $pending_assignments; ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Latest notice</div>
                <div class="stat-value" style="font-size:1.15rem;">
                    <?php echo $latest_notice_title ? htmlspecialchars($latest_notice_title) : '—'; ?>
                </div>
            </div>
        </div>

        <div class="quick-links">
            <a href="courses.php" class="quick-link-btn">View my courses →</a>
            <a href="assignments.php" class="quick-link-btn ghost">Check assignments →</a>
        </div>

        <div class="panel">
            <h3>Recent notices</h3>
            <?php if (empty($recent_notices)): ?>
                <p class="muted">No notices have been posted yet. Check back soon.</p>
            <?php else: ?>
                <?php foreach ($recent_notices as $n): ?>
                    <div class="notice-row">
                        <div class="notice-title"><?php echo htmlspecialchars($n['title']); ?></div>
                        <div class="notice-meta">
                            Posted by <?php echo htmlspecialchars($n['posted_by'] ?: 'Academy Staff'); ?> ·
                            <?php echo htmlspecialchars(date('d M Y, g:i A', strtotime($n['created_at']))); ?>
                        </div>
                        <p class="notice-body"><?php echo htmlspecialchars($n['content']); ?></p>
                    </div>
                <?php endforeach; ?>
                <div style="margin-top:10px;">
                    <a href="notices.php" style="font-size:0.86rem; font-weight:600; color:var(--navy-800); text-decoration:none;">View all notices →</a>
                </div>
            <?php endif; ?>
        </div>
    </main>
</div>

<script src="js/main.js"></script>
</body>
</html>
