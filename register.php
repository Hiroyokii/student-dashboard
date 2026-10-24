<?php
session_start();
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/helpers.php';

if (isset($_SESSION['user_id']))
    redirect('dashboard.php');
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first = trim($_POST['firstname'] ?? '');
    $last = trim($_POST['lastname'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';
    $type = 5;

    if ($password !== $confirm)
        $error = 'Passwords do not match.';
    elseif (strlen($password) < 6)
        $error = 'Password must be at least 6 characters.';
    else {
        $image = saveUserImage($username);
        if ($image['error'] !== '') {
            $error = $image['error'];
        } elseif ($image['path'] === null) {
            $error = 'Please choose a profile image.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $imagePath = $image['path'];
            $stmt = $conn->prepare('INSERT INTO tbl_users (FIRSTNAME,LASTNAME,EMAIL,USERNAME,PASSWORD,USERTYPE_ID,IMAGE_PATH) VALUES (?,?,?,?,?,?,?)');
            $stmt->bind_param('sssssis', $first, $last, $email, $username, $hash, $type, $imagePath);
            if ($stmt->execute()) redirect('login.php');
            $error = $conn->errno === 1062 ? 'Username or email already exists.' : 'Registration failed.';
        }
    }
}
$types = $conn->query('SELECT ID,USERTYPE FROM tbl_usertype ORDER BY USERTYPE');
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Register</title>
    <link rel="stylesheet" href="assets/style.css">
</head>

<body class="login-page">
    <div class="login-card">
        <div class="brand"><span class="brand-dot"></span> Student Manager</div>
        <h1>Create account</h1>
        <p>Register a user with a securely hashed password.</p>
        <?php if ($error): ?>
            <div class="alert error"><?= e($error) ?></div><?php endif; ?>
        <form method="post" enctype="multipart/form-data">
            <div class="form-grid">
                <div class="field"><label>First name</label><input class="input" name="firstname" required></div>
                <div class="field"><label>Last name</label><input class="input" name="lastname" required></div>
                <div class="field"><label>Email</label><input class="input" type="email" name="email" required></div>
                <div class="field"><label>Username</label><input class="input" name="username" required></div>
                <div class="field"><label>Profile image</label><input class="input" type="file" name="imageUpload" accept="image/*" required></div>
                <div class="field"><label>Password</label><input class="input" type="password" name="password" required
                        minlength="6"></div>
                <div class="field"><label>Confirm password</label><input class="input" type="password"
                        name="confirm_password" required minlength="6"></div>
            </div><button class="btn btn-primary" style="width:100%;margin-top:8px">Register as Student</button>
        </form>
        <br>
        <p class="note center">Already have an account? <a class="link" href="login.php">Log in</a></p>
    </div>
</body>

</html>