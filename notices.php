<?php
session_start();

if (!isset($_SESSION['student_id'])) {
    header('Location: login.php');
    exit;
}

require_once 'config/db.php';

$active_page = 'notices';
$page_title  = 'Notices';

$notices = [];
if ($result = mysqli_query($conn, "SELECT title, content, posted_by, created_at FROM notices ORDER BY created_at DESC")) {
    while ($row = mysqli_fetch_assoc($result)) {
        $notices[] = $row;
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
                <p class="eyebrow">Notice Board</p>
                <h1>Notices</h1>
                <p class="lede">Announcements from academy staff, newest first.</p>
            </div>
        </div>

        <?php if (empty($notices)): ?>
            <div class="empty-state">
                <div class="empty-icon">📌</div>
                <h3>No notices yet</h3>
                <p>Announcements posted by the academy will show up here.</p>
            </div>
        <?php else: ?>
            <?php foreach ($notices as $n): ?>
                <div class="notice-card">
                    <h3><?php echo htmlspecialchars($n['title']); ?></h3>
                    <div class="notice-card-meta">
                        Posted by <?php echo htmlspecialchars($n['posted_by'] ?: 'Academy Staff'); ?> ·
                        <?php echo htmlspecialchars(date('d M Y, g:i A', strtotime($n['created_at']))); ?>
                    </div>
                    <p class="notice-card-body"><?php echo htmlspecialchars($n['content']); ?></p>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </main>
</div>

<script src="js/main.js"></script>
</body>
</html>
