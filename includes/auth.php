<?php
require_once __DIR__ . '/bootstrap.php';

if (!isset($_SESSION['user_id'])) {
    $loginUrl = basename(dirname($_SERVER['SCRIPT_NAME'])) === 'pages' ? '../login.php' : 'login.php';
    header('Location: ' . $loginUrl);
    exit;
}

require_once __DIR__ . '/../config/db.php';
$stmt = $conn->prepare('SELECT u.FIRSTNAME,u.LASTNAME,u.USERNAME,u.EMAIL,u.IMAGE_PATH,t.USERTYPE FROM tbl_users u JOIN tbl_usertype t ON t.ID=u.USERTYPE_ID WHERE u.ID=?');
$stmt->bind_param('i', $_SESSION['user_id']);
$stmt->execute();
$authenticatedUser = $stmt->get_result()->fetch_assoc();
if (!$authenticatedUser) {
    session_destroy();
    header('Location: ' . (basename(dirname($_SERVER['SCRIPT_NAME'])) === 'pages' ? '../login.php' : 'login.php'));
    exit;
}
$_SESSION['fullname'] = $authenticatedUser['FIRSTNAME'] . ' ' . $authenticatedUser['LASTNAME'];
$_SESSION['first_name'] = $authenticatedUser['FIRSTNAME'];
$_SESSION['last_name'] = $authenticatedUser['LASTNAME'];
$_SESSION['username'] = $authenticatedUser['USERNAME'];
$_SESSION['email'] = $authenticatedUser['EMAIL'];
$_SESSION['image_path'] = $authenticatedUser['IMAGE_PATH'];
$_SESSION['usertype'] = $authenticatedUser['USERTYPE'];

$rolePages = [
    'Admin' => '*',
    'Registrar' => ['subjects.php', 'term.php', 'instructor.php', 'schoolyear.php'],
    'Dean' => ['subjects.php', 'section.php', 'instructor.php', 'yearlevel.php'],
    'Instructor' => ['subjects.php', 'section.php'],
    'Student' => ['subjects.php'],
];
$currentPage = strtolower(basename($_SERVER['PHP_SELF']));
$role = $_SESSION['usertype'];
if (!in_array($currentPage, ['dashboard.php', 'profile.php'], true) && ($rolePages[$role] ?? []) !== '*' && !in_array($currentPage, array_map('strtolower', $rolePages[$role] ?? []), true)) {
    http_response_code(403);
    exit('Access denied. Your account does not have permission to view this page.');
}
