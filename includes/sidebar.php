<?php
/**
 * Sidebar navigation — included on every protected student page.
 * Expects $active_page to be set by the including page (e.g. 'dashboard', 'courses').
 */
if (!isset($active_page)) {
    $active_page = '';
}

function nav_active($page, $active_page) {
    return $page === $active_page ? ' active' : '';
}
?>
<aside class="sidebar">
    <div class="brand-row">
        <span class="crest" aria-hidden="true" style="width:32px;height:32px;">
            <svg viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M32 3 L58 13 V30 C58 45 47 55 32 61 C17 55 6 45 6 30 V13 Z" fill="#16305F" stroke="#E4C97A" stroke-width="2"/>
                <path d="M32 14 L32 44 M20 24 L44 24" stroke="#E4C97A" stroke-width="2.5" stroke-linecap="round"/>
                <circle cx="32" cy="34" r="5" fill="#E4C97A"/>
            </svg>
        </span>
        <p class="brand-name">Forces Academy</p>
    </div>

    <nav>
        <a href="dashboard.php" class="nav-link<?php echo nav_active('dashboard', $active_page); ?>">
            <span class="nav-icon">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="9"/><rect x="14" y="3" width="7" height="5"/><rect x="14" y="12" width="7" height="9"/><rect x="3" y="16" width="7" height="5"/></svg>
            </span>
            Dashboard
        </a>
        <a href="courses.php" class="nav-link<?php echo nav_active('courses', $active_page); ?>">
            <span class="nav-icon">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
            </span>
            My Courses
        </a>
        <a href="assignments.php" class="nav-link<?php echo nav_active('assignments', $active_page); ?>">
            <span class="nav-icon">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
            </span>
            Assignments
        </a>
        <a href="results.php" class="nav-link<?php echo nav_active('results', $active_page); ?>">
            <span class="nav-icon">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3v18h18"/><path d="M18.7 8l-5.1 5.2-3-3L7 14"/></svg>
            </span>
            My Results
        </a>
        <a href="notices.php" class="nav-link<?php echo nav_active('notices', $active_page); ?>">
            <span class="nav-icon">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
            </span>
            Notices
        </a>
        <a href="timetable.php" class="nav-link<?php echo nav_active('timetable', $active_page); ?>">
            <span class="nav-icon">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
            </span>
            Timetable
        </a>
        <a href="profile.php" class="nav-link<?php echo nav_active('profile', $active_page); ?>">
            <span class="nav-icon">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            </span>
            My Profile
        </a>

        <div class="nav-divider"></div>

        <a href="logout.php" class="nav-link logout-link">
            <span class="nav-icon">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
            </span>
            Logout
        </a>
    </nav>
</aside>
