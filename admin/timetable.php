<?php
require_once 'includes/auth.php';
require_admin_login();
require_once '../config/db.php';

$DAYS = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

// -----------------------------------------------------------------
// Handle add / delete (Post/Redirect/Get pattern)
// -----------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (isset($_POST['save_entry'])) {
        $class     = trim($_POST['class'] ?? '');
        $day       = trim($_POST['day'] ?? '');
        $time_slot = trim($_POST['time_slot'] ?? '');
        $subject   = trim($_POST['subject'] ?? '');
        $teacher   = trim($_POST['teacher'] ?? '');

        if ($class === '' || $day === '' || $time_slot === '' || $subject === '' || $teacher === '') {
            $_SESSION['flash_error'] = 'All fields are required.';
        } else {
            $stmt = mysqli_prepare($conn, "INSERT INTO timetable (class, day, time_slot, subject, teacher) VALUES (?, ?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt, 'sssss', $class, $day, $time_slot, $subject, $teacher);
            if (mysqli_stmt_execute($stmt)) {
                $_SESSION['flash_success'] = 'Timetable entry added successfully.';
            } else {
                $_SESSION['flash_error'] = 'Could not add the timetable entry.';
            }
            mysqli_stmt_close($stmt);
        }

        header('Location: timetable.php');
        exit;
    }

    if (isset($_POST['delete_entry'])) {
        $entry_id = (int) ($_POST['entry_id'] ?? 0);
        if ($entry_id > 0) {
            $stmt = mysqli_prepare($conn, "DELETE FROM timetable WHERE id = ?");
            mysqli_stmt_bind_param($stmt, 'i', $entry_id);
            if (mysqli_stmt_execute($stmt)) {
                $_SESSION['flash_success'] = 'Timetable entry deleted successfully.';
            } else {
                $_SESSION['flash_error'] = 'Could not delete the timetable entry.';
            }
            mysqli_stmt_close($stmt);
        }
        header('Location: timetable.php');
        exit;
    }
}

$flash_success = $_SESSION['flash_success'] ?? null;
$flash_error   = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_success'], $_SESSION['flash_error']);

// -----------------------------------------------------------------
// Classes for the "select class" dropdown — pulled from students,
// falling back to the classes already used in the timetable.
// -----------------------------------------------------------------
$classes = [];
$seen    = [];
if ($result = mysqli_query($conn, "SELECT DISTINCT class FROM students ORDER BY class")) {
    while ($row = mysqli_fetch_assoc($result)) {
        if (!isset($seen[$row['class']])) {
            $classes[] = $row['class'];
            $seen[$row['class']] = true;
        }
    }
}
if ($result = mysqli_query($conn, "SELECT DISTINCT class FROM timetable ORDER BY class")) {
    while ($row = mysqli_fetch_assoc($result)) {
        if (!isset($seen[$row['class']])) {
            $classes[] = $row['class'];
            $seen[$row['class']] = true;
        }
    }
}

// -----------------------------------------------------------------
// All timetable entries
// -----------------------------------------------------------------
$entries = [];
if ($result = mysqli_query($conn, "SELECT id, class, day, time_slot, subject, teacher FROM timetable ORDER BY class, FIELD(day,'Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'), time_slot")) {
    while ($row = mysqli_fetch_assoc($result)) {
        $entries[] = $row;
    }
}

$active_page   = 'timetable';
$page_title    = 'Manage Timetable';
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
                <p class="eyebrow">Class Schedule</p>
                <h1>Manage Timetable</h1>
                <p class="lede">Add class periods so students can see their weekly schedule.</p>
            </div>
        </div>

        <?php if ($flash_success): ?>
            <div class="alert-academy success" role="status"><span>✓</span><span><?php echo htmlspecialchars($flash_success); ?></span></div>
        <?php endif; ?>
        <?php if ($flash_error): ?>
            <div class="alert-academy danger" role="alert"><span>⚠</span><span><?php echo htmlspecialchars($flash_error); ?></span></div>
        <?php endif; ?>

        <div class="panel">
            <h3>Add Timetable Entry</h3>
            <form method="POST" action="timetable.php" data-guard novalidate>
                <div class="admin-form-grid">
                    <div class="field">
                        <label for="class">Class</label>
                        <?php if (!empty($classes)): ?>
                            <select class="form-control" id="class" name="class" required>
                                <option value="">Select class</option>
                                <?php foreach ($classes as $c): ?>
                                    <option value="<?php echo htmlspecialchars($c); ?>"><?php echo htmlspecialchars($c); ?></option>
                                <?php endforeach; ?>
                                <option value="__other">Other (type below)</option>
                            </select>
                            <input class="form-control" type="text" id="class_other" name="class_other" placeholder="Type class name" style="margin-top:8px; display:none;">
                        <?php else: ?>
                            <input class="form-control" type="text" id="class" name="class" placeholder="e.g. Full Stack — SI-26" required>
                        <?php endif; ?>
                    </div>
                    <div class="field">
                        <label for="day">Day</label>
                        <select class="form-control" id="day" name="day" required>
                            <option value="">Select day</option>
                            <?php foreach ($DAYS as $d): ?>
                                <option value="<?php echo $d; ?>"><?php echo $d; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="field">
                        <label for="time_slot">Time slot</label>
                        <input class="form-control" type="text" id="time_slot" name="time_slot" placeholder="e.g. 9:00 - 10:00 AM" required>
                    </div>
                    <div class="field">
                        <label for="subject">Subject</label>
                        <input class="form-control" type="text" id="subject" name="subject" placeholder="e.g. PHP & MySQL" required>
                    </div>
                    <div class="field">
                        <label for="teacher">Teacher</label>
                        <input class="form-control" type="text" id="teacher" name="teacher" placeholder="e.g. Sir Ahmed Raza" required>
                    </div>
                </div>

                <div class="admin-form-actions">
                    <button type="submit" name="save_entry" value="1" class="btn-academy" data-loading-text="Saving...">Add Entry</button>
                </div>
            </form>
        </div>

        <div class="panel">
            <div class="panel-head">
                <h3>All Timetable Entries</h3>
                <span class="count-tag"><?php echo count($entries); ?> total</span>
            </div>

            <?php if (empty($entries)): ?>
                <div class="empty-state">
                    <div class="empty-icon">🗓️</div>
                    <h3>No timetable entries yet</h3>
                    <p>Add the first period using the form above.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle mb-0">
                        <thead>
                            <tr><th>Class</th><th>Day</th><th>Time Slot</th><th>Subject</th><th>Teacher</th><th>Actions</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($entries as $e): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($e['class']); ?></td>
                                    <td><?php echo htmlspecialchars($e['day']); ?></td>
                                    <td><?php echo htmlspecialchars($e['time_slot']); ?></td>
                                    <td><?php echo htmlspecialchars($e['subject']); ?></td>
                                    <td><?php echo htmlspecialchars($e['teacher']); ?></td>
                                    <td>
                                        <div class="action-btns">
                                            <button type="button" class="btn-action danger" data-bs-toggle="modal" data-bs-target="#deleteEntry<?php echo (int) $e['id']; ?>">Delete</button>
                                        </div>
                                    </td>
                                </tr>

                                <div class="modal fade" id="deleteEntry<?php echo (int) $e['id']; ?>" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Delete timetable entry?</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <p>You're about to permanently delete <strong><?php echo htmlspecialchars($e['subject']); ?></strong> (<?php echo htmlspecialchars($e['day']); ?>, <?php echo htmlspecialchars($e['time_slot']); ?>) for <strong><?php echo htmlspecialchars($e['class']); ?></strong>.</p>
                                                <p class="text-danger-academy">This cannot be undone.</p>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn-action" data-bs-dismiss="modal">Cancel</button>
                                                <form method="POST" action="timetable.php" style="margin:0;">
                                                    <input type="hidden" name="entry_id" value="<?php echo (int) $e['id']; ?>">
                                                    <button type="submit" name="delete_entry" value="1" class="btn-action danger">Yes, delete</button>
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
<script>
  // When "Other (type below)" is picked in the class dropdown, swap in
  // the free-text field and submit that value instead.
  (function () {
    var select = document.getElementById('class');
    var other  = document.getElementById('class_other');
    if (!select || !other) return;

    select.addEventListener('change', function () {
      if (select.value === '__other') {
        other.style.display = 'block';
        other.required = true;
        select.removeAttribute('name');
        other.setAttribute('name', 'class');
      } else {
        other.style.display = 'none';
        other.required = false;
        other.removeAttribute('name');
        select.setAttribute('name', 'class');
      }
    });
  })();
</script>
</body>
</html>
