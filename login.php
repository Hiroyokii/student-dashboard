<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/helpers.php';

if (isset($_SESSION['user_id']))
    redirect('dashboard.php');

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $conn->prepare('SELECT u.ID,u.FIRSTNAME,u.LASTNAME,u.USERNAME,u.PASSWORD,u.IMAGE_PATH,t.USERTYPE FROM tbl_users u JOIN tbl_usertype t ON t.ID=u.USERTYPE_ID WHERE u.USERNAME = ? LIMIT 1');
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();

    if ($user && password_verify($password, $user['PASSWORD'])) {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['ID'];
        $_SESSION['username'] = $user['USERNAME'];
        $_SESSION['fullname'] = $user['FIRSTNAME'] . ' ' . $user['LASTNAME'];
        $_SESSION['usertype'] = $user['USERTYPE'];
        $_SESSION['image_path'] = $user['IMAGE_PATH'] ?? '';
        redirect('dashboard.php');
    }
    $error = 'Invalid username or password.';
}
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Log in</title>
    <link rel="stylesheet" href="assets/style.css">
</head>

<body class="login-page">
    <div class="login-card">
        <div class="brand"><span class="brand-dot"></span> Student Manager</div>
        <h1>Welcome back</h1>
        <p>Log in to manage the school records.</p>
        <?php if ($error): ?>
            <div class="alert error"><?= e($error) ?></div><?php endif; ?>
        <form method="post">
            <div class="field"><label>Username</label><input class="input" name="username" required></div>
            <div class="field"><label>Password</label><input class="input" type="password" name="password" required>
            </div><button class="btn btn-primary" style="width:100%">Log in</button>
        </form>
        <br>
        <p class="note center">New account? <a class="link" href="register.php">Register here</a></p>
    </div>
</body>

</html>
