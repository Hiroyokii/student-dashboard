<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/helpers.php';

function profileReturnPath(): string
{
    $path = $_POST['return_to'] ?? 'dashboard.php';
    if (!is_string($path) || $path === '' || $path[0] !== '/' || str_starts_with($path, '//')) {
        return 'dashboard.php';
    }
    return $path;
}

$returnTo = profileReturnPath();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('dashboard.php');
}

$first = trim($_POST['firstname'] ?? '');
$last = trim($_POST['lastname'] ?? '');
$email = trim($_POST['email'] ?? '');
$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';
$userId = (int) $_SESSION['user_id'];

if ($first === '' || $last === '' || $email === '' || $username === '') {
    flash('error', 'Please complete your name, email, and username.');
    redirect($returnTo);
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    flash('error', 'Please enter a valid email address.');
    redirect($returnTo);
}
if ($password !== '' && strlen($password) < 6) {
    flash('error', 'A new password must be at least 6 characters.');
    redirect($returnTo);
}

$stmt = $conn->prepare('SELECT USERNAME,IMAGE_PATH FROM tbl_users WHERE ID=?');
$stmt->bind_param('i', $userId);
$stmt->execute();
$currentUser = $stmt->get_result()->fetch_assoc();
if (!$currentUser) {
    flash('error', 'Your account could not be found.');
    redirect('login.php');
}

$image = saveUserImage($username);
if ($image['error'] !== '') {
    flash('error', $image['error']);
    redirect($returnTo);
}
$imagePath = $image['path'] ?? ($currentUser['IMAGE_PATH'] ?? '');
$oldImageToRemove = '';
if ($image['path'] === null && $username !== $currentUser['USERNAME'] && $imagePath !== '') {
    $oldRelativePath = str_replace('\\', '/', $imagePath);
    if (str_starts_with($oldRelativePath, 'images/users/')) {
        $safeUsername = preg_replace('/[^A-Za-z0-9._-]/', '_', $username);
        $newRelativePath = 'images/users/' . $safeUsername . '.JPG';
        $oldAbsolutePath = __DIR__ . '/' . $oldRelativePath;
        $newAbsolutePath = __DIR__ . '/' . $newRelativePath;
        if (is_file($oldAbsolutePath) && $oldAbsolutePath !== $newAbsolutePath) {
            if (!copy($oldAbsolutePath, $newAbsolutePath)) {
                flash('error', 'Your profile image could not be renamed.');
                redirect($returnTo);
            }
            $oldImageToRemove = $oldAbsolutePath;
            $imagePath = $newRelativePath;
        }
    }
}

if ($password !== '') {
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare('UPDATE tbl_users SET FIRSTNAME=?,LASTNAME=?,EMAIL=?,USERNAME=?,PASSWORD=?,IMAGE_PATH=? WHERE ID=?');
    $stmt->bind_param('ssssssi', $first, $last, $email, $username, $hash, $imagePath, $userId);
} else {
    $stmt = $conn->prepare('UPDATE tbl_users SET FIRSTNAME=?,LASTNAME=?,EMAIL=?,USERNAME=?,IMAGE_PATH=? WHERE ID=?');
    $stmt->bind_param('sssssi', $first, $last, $email, $username, $imagePath, $userId);
}

if (!$stmt->execute()) {
    flash('error', $conn->errno === 1062 ? 'That username or email is already in use.' : 'Your profile could not be updated.');
    redirect($returnTo);
}
if ($oldImageToRemove !== '') {
    @unlink($oldImageToRemove);
}
$_SESSION['fullname'] = $first . ' ' . $last;
$_SESSION['first_name'] = $first;
$_SESSION['last_name'] = $last;
$_SESSION['username'] = $username;
$_SESSION['email'] = $email;
$_SESSION['image_path'] = $imagePath;
flash('success', 'Your profile was updated.');
redirect($returnTo);
