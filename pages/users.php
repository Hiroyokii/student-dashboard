<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';

$pageTitle = 'Users';
$id = (int) ($_GET['id'] ?? 0);
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save') {
    $first = trim($_POST['firstname'] ?? '');
    $last = trim($_POST['lastname'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $type = (int) ($_POST['usertype_id'] ?? 0);
    $image = saveUserImage($username);

    if (!$first || !$last || !$email || !$username || !$type) {
        $error = 'Please complete all user fields.';
    } elseif ($image['error'] !== '') {
        $error = $image['error'];
    } elseif (!$id && $image['path'] === null) {
        $error = 'Please choose a profile image.';
    } elseif (!$id && strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } else {
        if ($id) {
            if ($image['path'] !== null) {
                $imagePath = $image['path'];
                if ($password !== '') {
                    $hash = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $conn->prepare('UPDATE tbl_users SET FIRSTNAME=?,LASTNAME=?,EMAIL=?,USERNAME=?,PASSWORD=?,USERTYPE_ID=?,IMAGE_PATH=? WHERE ID=?');
                    $stmt->bind_param('sssssisi', $first, $last, $email, $username, $hash, $type, $imagePath, $id);
                } elseif ($password === '') {
                    $stmt = $conn->prepare('UPDATE tbl_users SET FIRSTNAME=?,LASTNAME=?,EMAIL=?,USERNAME=?,USERTYPE_ID=?,IMAGE_PATH=? WHERE ID=?');
                    $stmt->bind_param('ssssisi', $first, $last, $email, $username, $type, $imagePath, $id);
                }
            } elseif ($password !== '') {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare('UPDATE tbl_users SET FIRSTNAME=?,LASTNAME=?,EMAIL=?,USERNAME=?,PASSWORD=?,USERTYPE_ID=? WHERE ID=?');
                $stmt->bind_param('sssssii', $first, $last, $email, $username, $hash, $type, $id);
            } else {
                $stmt = $conn->prepare('UPDATE tbl_users SET FIRSTNAME=?,LASTNAME=?,EMAIL=?,USERNAME=?,USERTYPE_ID=? WHERE ID=?');
                $stmt->bind_param('ssssii', $first, $last, $email, $username, $type, $id);
            }
            if ($stmt->execute()) {
                flash('success', 'User updated.');
                redirect('users.php');
            }
            $error = 'Update failed. Check duplicate username or email.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $imagePath = $image['path'];
            $stmt = $conn->prepare('INSERT INTO tbl_users (FIRSTNAME,LASTNAME,EMAIL,USERNAME,PASSWORD,USERTYPE_ID,IMAGE_PATH) VALUES (?,?,?,?,?,?,?)');
            $stmt->bind_param('sssssis', $first, $last, $email, $username, $hash, $type, $imagePath);
            if ($stmt->execute()) {
                flash('success', 'User added.');
                redirect('users.php');
            }
            $error = 'Add failed. Check duplicate username or email.';
        }
    }
}

if (isset($_GET['delete'])) {
    $delete = (int) $_GET['delete'];
    $stmt = $conn->prepare('DELETE FROM tbl_users WHERE ID=?');
    $stmt->bind_param('i', $delete);
    if ($stmt->execute()) flash('success', 'User deleted.');
    else flash('error', 'Delete failed.');
    redirect('users.php');
}

$edit = null;
if ($id) {
    $stmt = $conn->prepare('SELECT * FROM tbl_users WHERE ID=?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $edit = $stmt->get_result()->fetch_assoc();
}
$types = $conn->query('SELECT ID,USERTYPE FROM tbl_usertype ORDER BY USERTYPE');
$q = trim($_GET['q'] ?? '');
$like = "%$q%";
$stmt = $conn->prepare('SELECT u.ID,u.FIRSTNAME,u.LASTNAME,u.EMAIL,u.USERNAME,u.IMAGE_PATH,t.USERTYPE FROM tbl_users u JOIN tbl_usertype t ON t.ID=u.USERTYPE_ID WHERE u.FIRSTNAME LIKE ? OR u.LASTNAME LIKE ? OR u.EMAIL LIKE ? OR u.USERNAME LIKE ? OR t.USERTYPE LIKE ? ORDER BY u.ID DESC');
$stmt->bind_param('sssss', $like, $like, $like, $like, $like);
$stmt->execute();
$result = $stmt->get_result();

include __DIR__ . '/../includes/header.php';
showFlash();
if ($error) echo '<div class="alert error">' . e($error) . '</div>';
?>
<div class="page-head"><div><h1>Users</h1><p>Administer accounts by role. Passwords are stored as secure hashes.</p></div><?php if (!$edit): ?><button class="btn btn-primary" type="button" onclick="document.getElementById('record-form-modal').showModal()">Add user</button><?php endif; ?></div>
<dialog class="record-modal" id="record-form-modal"><div class="modal-head"><h2><?= $edit ? 'Edit user' : 'Add user' ?></h2><button class="modal-close" type="button" onclick="this.closest('dialog').close()">×</button></div><div class="modal-body">
<form class="record-form" method="post" enctype="multipart/form-data" action="<?= $edit ? '?id=' . $id : '' ?>"><input type="hidden" name="action" value="save"><div class="form-grid">
<div class="field"><label>First Name</label><input class="input" name="firstname" value="<?= e($edit['FIRSTNAME'] ?? '') ?>" required></div>
<div class="field"><label>Last Name</label><input class="input" name="lastname" value="<?= e($edit['LASTNAME'] ?? '') ?>" required></div>
<div class="field"><label>Email</label><input class="input" type="email" name="email" value="<?= e($edit['EMAIL'] ?? '') ?>" required></div>
<div class="field"><label>Username</label><input class="input" name="username" value="<?= e($edit['USERNAME'] ?? '') ?>" required></div>
<div class="field"><label>Profile image <?= $edit ? '(leave blank to keep current)' : '' ?></label><input class="input" type="file" name="imageUpload" accept="image/*" <?= $edit ? '' : 'required' ?>></div>
<div class="field"><label>Password <?= $edit ? '(leave blank to keep current)' : '' ?></label><input class="input" type="password" name="password" <?= $edit ? '' : 'required minlength="6"' ?>></div>
<div class="field"><label>User Type</label><select class="select" name="usertype_id" required><?php while ($typeRow = $types->fetch_assoc()): ?><option value="<?= $typeRow['ID'] ?>" <?= $edit && $edit['USERTYPE_ID'] == $typeRow['ID'] ? 'selected' : '' ?>><?= e($typeRow['USERTYPE']) ?></option><?php endwhile; ?></select></div>
</div><div class="form-actions modal-actions"><button class="btn btn-light" type="button" onclick="this.closest('dialog').close()">Cancel</button><button class="btn btn-primary"><?= $edit ? 'Save changes' : 'Add user' ?></button></div></form></div></dialog>
<?php if ($edit): ?><script>document.getElementById('record-form-modal').showModal()</script><?php endif; ?>
<div class="card"><div class="toolbar"><form class="search" method="get"><input class="input" name="q" value="<?= e($q) ?>" placeholder="Search users..."><button class="btn btn-light">Search</button></form><span class="note"><?= $result->num_rows ?> record(s)</span></div><div class="table-wrap"><table class="table"><thead><tr><th>Photo</th><th>ID</th><th>Name</th><th>Email</th><th>Username</th><th>User Type</th><th>Actions</th></tr></thead><tbody><?php while ($row = $result->fetch_assoc()): ?><tr><td><?php if (!empty($row['IMAGE_PATH'])): ?><img class="user-avatar" src="../<?= e($row['IMAGE_PATH']) ?>" alt=""><?php endif; ?></td><td><?= $row['ID'] ?></td><td><?= e($row['FIRSTNAME'] . ' ' . $row['LASTNAME']) ?></td><td><?= e($row['EMAIL']) ?></td><td><?= e($row['USERNAME']) ?></td><td><?= e($row['USERTYPE']) ?></td><td><div class="actions"><a class="small-btn edit" href="?id=<?= $row['ID'] ?>">Edit</a><a class="small-btn delete" href="?delete=<?= $row['ID'] ?>">Delete</a></div></td></tr><?php endwhile; ?></tbody></table></div></div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
