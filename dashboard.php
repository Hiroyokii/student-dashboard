<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';

$pageTitle = 'Dashboard';

$tables = [
    ['name' => 'Subjects', 'table' => 'tbl_subjects', 'link' => 'subjects.php'],
    ['name' => 'Term', 'table' => 'tbl_term', 'link' => 'term.php'],
    ['name' => 'Instructors', 'table' => 'tbl_instructors', 'link' => 'instructor.php'],
    ['name' => 'School Year', 'table' => 'tbl_schoolyear', 'link' => 'schoolyear.php'],
    ['name' => 'Section', 'table' => 'tbl_section', 'link' => 'section.php'],
    ['name' => 'Year Level', 'table' => 'tbl_yearlevel', 'link' => 'yearlevel.php'],
    ['name' => 'Course', 'table' => 'tbl_course', 'link' => 'course.php'],
    ['name' => 'Users', 'table' => 'tbl_users', 'link' => 'users.php'],
    ['name' => 'User Type', 'table' => 'tbl_usertype', 'link' => 'usertype.php']
];
$dashboardAccess = ['Admin' => '*', 'Registrar' => ['subjects.php', 'term.php', 'instructor.php', 'schoolyear.php'], 'Dean' => ['subjects.php', 'section.php', 'instructor.php', 'yearlevel.php'], 'Instructor' => ['subjects.php', 'section.php'], 'Student' => ['subjects.php']];
if (($dashboardAccess[$_SESSION['usertype'] ?? ''] ?? []) !== '*') {
    $allowed = $dashboardAccess[$_SESSION['usertype'] ?? ''] ?? [];
    $tables = array_values(array_filter($tables, fn($t) => in_array($t['link'], $allowed, true)));
}

foreach ($tables as &$t) {
    $result = $conn->query("SELECT COUNT(*) AS c FROM {$t['table']}");
    $t['count'] = (int) $result->fetch_assoc()['c'];
}
unset($t);

include __DIR__ . '/includes/header.php';
showFlash();
?>

<div class="card">
    <div>
        <h1>Dashboard</h1>
        <p>Welcome, <?= e($_SESSION['fullname']) ?>. Manage your school records in one place.</p>
    </div>
</div>

<div class="stats">
    <?php foreach ($tables as $t): ?>
        <a class="stat" href="pages/<?= e($t['link']) ?>">
            <span><?= e($t['name']) ?></span>
            <strong><?= $t['count'] ?></strong>
            <small>Manage records →</small>
        </a>
    <?php endforeach; ?>
</div>

<div class="card">
    <div class="page-head compact-head">
        <div>
            <h2>Quick access</h2>
            <p>Each section supports Add, Search, Update, and Delete.</p>
        </div>
    </div>

    <div class="quick">
        <?php foreach ($tables as $t): ?>
            <a href="pages/<?= e($t['link']) ?>">
                <?= e($t['name']) ?>
                <span>→</span>
            </a>
        <?php endforeach; ?>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>