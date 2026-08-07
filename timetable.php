<?php
session_start();

if (!isset($_SESSION['student_id'])) {
    header('Location: login.php');
    exit;
}

require_once 'config/db.php';

$active_page = 'timetable';
$page_title  = 'My Timetable';

// Fetch the logged-in student's class
$stmt = mysqli_prepare($conn, "SELECT class FROM students WHERE id = ?");
mysqli_stmt_bind_param($stmt, 'i', $_SESSION['student_id']);
mysqli_stmt_execute($stmt);
$student = mysqli_stmt_get_result($stmt)->fetch_assoc();
mysqli_stmt_close($stmt);

$student_class = $student['class'] ?? '';

// Pull all timetable entries for this class
$entries = [];
$stmt = mysqli_prepare($conn, "SELECT day, time_slot, subject, teacher FROM timetable WHERE class = ?");
mysqli_stmt_bind_param($stmt, 's', $student_class);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
while ($row = mysqli_fetch_assoc($result)) {
    $entries[] = $row;
}
mysqli_stmt_close($stmt);

// Build a day x time-slot grid
$DAYS = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

$time_slots = [];
foreach ($entries as $e) {
    if (!in_array($e['time_slot'], $time_slots, true)) {
        $time_slots[] = $e['time_slot'];
    }
}
sort($time_slots);

$grid = []; // $grid[time_slot][day] = ['subject'=>..,'teacher'=>..]
foreach ($entries as $e) {
    $grid[$e['time_slot']][$e['day']] = ['subject' => $e['subject'], 'teacher' => $e['teacher']];
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
                <p class="eyebrow">Weekly Schedule</p>
                <h1>My Timetable</h1>
                <p class="lede">Class: <strong><?php echo htmlspecialchars($student_class ?: '—'); ?></strong></p>
            </div>
        </div>

        <?php if (empty($entries)): ?>
            <div class="empty-state">
                <div class="empty-icon">🗓️</div>
                <h3>No timetable published yet</h3>
                <p>Once the academy adds periods for your class, they'll appear here.</p>
            </div>
        <?php else: ?>
            <div class="panel">
                <div class="table-responsive">
                    <table class="table timetable-grid mb-0">
                        <thead>
                            <tr>
                                <th>Time</th>
                                <?php foreach ($DAYS as $d): ?>
                                    <th><?php echo $d; ?></th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($time_slots as $slot): ?>
                                <tr>
                                    <td class="tt-time"><?php echo htmlspecialchars($slot); ?></td>
                                    <?php foreach ($DAYS as $d): ?>
                                        <?php $cell = $grid[$slot][$d] ?? null; ?>
                                        <td>
                                            <?php if ($cell): ?>
                                                <div class="tt-cell">
                                                    <span class="tt-subject"><?php echo htmlspecialchars($cell['subject']); ?></span>
                                                    <span class="tt-teacher"><?php echo htmlspecialchars($cell['teacher']); ?></span>
                                                </div>
                                            <?php else: ?>
                                                <span class="tt-empty">—</span>
                                            <?php endif; ?>
                                        </td>
                                    <?php endforeach; ?>
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
