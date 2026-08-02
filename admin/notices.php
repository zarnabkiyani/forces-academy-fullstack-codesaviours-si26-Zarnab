<?php
require_once 'includes/auth.php';
require_admin_login();
require_once '../config/db.php';

// -----------------------------------------------------------------
// Handle post / delete (Post/Redirect/Get pattern)
// -----------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['post_notice'])) {
    $title   = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');

    if ($title === '' || $content === '') {
        $_SESSION['flash_error'] = 'Both a title and content are required.';
    } else {
        $posted_by = $_SESSION['admin_username'];
        $stmt = mysqli_prepare($conn, "INSERT INTO notices (title, content, posted_by) VALUES (?, ?, ?)");
        mysqli_stmt_bind_param($stmt, 'sss', $title, $content, $posted_by);
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['flash_success'] = 'Notice posted successfully.';
        } else {
            $_SESSION['flash_error'] = 'Could not post the notice. Please try again.';
        }
        mysqli_stmt_close($stmt);
    }

    header('Location: notices.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_notice'])) {
    $notice_id = (int) ($_POST['notice_id'] ?? 0);
    if ($notice_id > 0) {
        $stmt = mysqli_prepare($conn, "DELETE FROM notices WHERE id = ?");
        mysqli_stmt_bind_param($stmt, 'i', $notice_id);
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['flash_success'] = 'Notice deleted.';
        } else {
            $_SESSION['flash_error'] = 'Could not delete the notice.';
        }
        mysqli_stmt_close($stmt);
    }
    header('Location: notices.php');
    exit;
}

$flash_success = $_SESSION['flash_success'] ?? null;
$flash_error   = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_success'], $_SESSION['flash_error']);

$notices = [];
if ($result = mysqli_query($conn, "SELECT id, title, content, posted_by, created_at FROM notices ORDER BY created_at DESC")) {
    while ($row = mysqli_fetch_assoc($result)) {
        $notices[] = $row;
    }
}

$active_page   = 'notices';
$page_title    = 'Post Notice';
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
                <p class="eyebrow">Notice Board</p>
                <h1>Post Notice</h1>
                <p class="lede">Announce something to every student at once.</p>
            </div>
        </div>

        <?php if ($flash_success): ?>
            <div class="alert-academy success" role="status"><span>✓</span><span><?php echo htmlspecialchars($flash_success); ?></span></div>
        <?php endif; ?>
        <?php if ($flash_error): ?>
            <div class="alert-academy danger" role="alert"><span>⚠</span><span><?php echo htmlspecialchars($flash_error); ?></span></div>
        <?php endif; ?>

        <div class="panel">
            <h3>New Notice</h3>
            <form method="POST" action="notices.php" data-guard novalidate>
                <div class="field">
                    <label for="title">Title</label>
                    <input class="form-control" type="text" id="title" name="title" placeholder="e.g. Week 5 task released" required>
                </div>
                <div class="field">
                    <label for="content">Content</label>
                    <textarea class="form-control" id="content" name="content" rows="4" placeholder="Write the announcement..." required></textarea>
                </div>
                <div class="admin-form-actions">
                    <button type="submit" name="post_notice" value="1" class="btn-academy" data-loading-text="Posting...">Post Notice</button>
                </div>
            </form>
        </div>

        <div class="panel">
            <div class="panel-head">
                <h3>All Notices</h3>
                <span class="count-tag"><?php echo count($notices); ?> total</span>
            </div>

            <?php if (empty($notices)): ?>
                <p class="muted">No notices posted yet.</p>
            <?php else: ?>
                <?php foreach ($notices as $n): ?>
                    <div class="notice-row">
                        <div class="notice-title"><?php echo htmlspecialchars($n['title']); ?></div>
                        <div class="notice-meta">
                            Posted by <?php echo htmlspecialchars($n['posted_by'] ?: 'Academy Staff'); ?> ·
                            <?php echo htmlspecialchars(date('d M Y, g:i A', strtotime($n['created_at']))); ?>
                        </div>
                        <p class="notice-body"><?php echo htmlspecialchars($n['content']); ?></p>
                        <div class="action-btns">
                            <button type="button" class="btn-action danger" data-bs-toggle="modal" data-bs-target="#deleteNotice<?php echo (int) $n['id']; ?>">Delete</button>
                        </div>
                    </div>

                    <div class="modal fade" id="deleteNotice<?php echo (int) $n['id']; ?>" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Delete notice?</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <p>Delete "<strong><?php echo htmlspecialchars($n['title']); ?></strong>"? This cannot be undone.</p>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn-action" data-bs-dismiss="modal">Cancel</button>
                                    <form method="POST" action="notices.php" style="margin:0;">
                                        <input type="hidden" name="notice_id" value="<?php echo (int) $n['id']; ?>">
                                        <button type="submit" name="delete_notice" value="1" class="btn-action danger">Yes, delete</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="../js/main.js"></script>
</body>
</html>
