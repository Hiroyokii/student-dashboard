<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';
$pageTitle = 'Instructors';
$id = (int) ($_GET['id'] ?? 0);
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save') {
    $salutation = trim($_POST['salutation'] ?? '');
    $first = trim($_POST['firstname'] ?? '');
    $middle = trim($_POST['middlename'] ?? '');
    $last = trim($_POST['lastname'] ?? '');
    $photo = trim($_POST['current_photo'] ?? '');
    if (!empty($_FILES['photo']['name'])) {
        $mime = (new finfo(FILEINFO_MIME_TYPE))->file($_FILES['photo']['tmp_name']);
        $extensions = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
        if (!isset($extensions[$mime]))
            $error = 'Photo must be a JPG, PNG, or WebP image.';
        elseif ($_FILES['photo']['error'] !== UPLOAD_ERR_OK)
            $error = 'Photo upload failed.';
        else {
            $dir = __DIR__ . '/../assets/uploads';
            if (!is_dir($dir))
                mkdir($dir, 0755, true);
            $photo = 'assets/uploads/instructor_' . bin2hex(random_bytes(8)) . '.' . $extensions[$mime];
            if (!move_uploaded_file($_FILES['photo']['tmp_name'], __DIR__ . '/../' . $photo))
                $error = 'Could not save photo.';
        }
    }
    if (!$error && (!$salutation || !$first || !$last))
        $error = 'Salutation, first name, and last name are required.';
    if (!$error && $id) {
        $stmt = $conn->prepare('UPDATE tbl_instructors SET SALUTATION=?,FIRSTNAME=?,MIDDLENAME=?,LASTNAME=?,PHOTO=? WHERE ID=?');
        $stmt->bind_param('sssssi', $salutation, $first, $middle, $last, $photo, $id);
        if ($stmt->execute()) {
            flash('success', 'Instructor updated.');
            redirect('instructor.php');
        }
        $error = 'Update failed.';
    } elseif (!$error) {
        $stmt = $conn->prepare('INSERT INTO tbl_instructors (SALUTATION,FIRSTNAME,MIDDLENAME,LASTNAME,PHOTO) VALUES (?,?,?,?,?)');
        $stmt->bind_param('sssss', $salutation, $first, $middle, $last, $photo);
        if ($stmt->execute()) {
            flash('success', 'Instructor added.');
            redirect('instructor.php');
        }
        $error = 'Add failed.';
    }
}
if (isset($_GET['delete'])) {
    $delete = (int) $_GET['delete'];
    $stmt = $conn->prepare('DELETE FROM tbl_instructors WHERE ID=?');
    $stmt->bind_param('i', $delete);
    if ($stmt->execute())
        flash('success', 'Instructor deleted.');
    else
        flash('error', 'Delete failed.');
    redirect('instructor.php');
}
$edit = null;
if ($id) {
    $stmt = $conn->prepare('SELECT * FROM tbl_instructors WHERE ID=?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $edit = $stmt->get_result()->fetch_assoc();
}
$q = trim($_GET['q'] ?? '');
$like = "%$q%";
$stmt = $conn->prepare('SELECT * FROM tbl_instructors WHERE SALUTATION LIKE ? OR FIRSTNAME LIKE ? OR MIDDLENAME LIKE ? OR LASTNAME LIKE ? ORDER BY ID DESC');
$stmt->bind_param('ssss', $like, $like, $like, $like);
$stmt->execute();
$result = $stmt->get_result();
include __DIR__ . '/../includes/header.php';
showFlash();
if ($error)
    echo '<div class="alert error">' . e($error) . '</div>';
?>
<div class="page-head">
    <div>
        <h1>Instructors</h1>
        <p>Manage instructor profiles and photos.</p>
    </div><?php if (!$edit): ?><button type="button" class="btn btn-primary"
            onclick="document.getElementById('instructor-create').showModal()">Add instructor</button><?php endif; ?>
</div>
<dialog class="record-modal" id="instructor-create">
    <div class="modal-head">
        <h2><?= $edit ? 'Edit instructor' : 'Add instructor' ?></h2><button class="modal-close" type="button"
            onclick="this.closest('dialog').close()">×</button>
    </div>
    <div class="modal-body">
        <form method="post" enctype="multipart/form-data"><input type="hidden" name="action" value="save"><input
                type="hidden" name="current_photo" value="<?= e($edit['PHOTO'] ?? '') ?>">
            <div class="form-grid">
                <div class="field"><label>Salutation</label><input class="input" name="salutation"
                        placeholder="Dr., Mr., Ms." value="<?= e($edit['SALUTATION'] ?? '') ?>" required></div>
                <div class="field"><label>First name</label><input class="input" name="firstname"
                        value="<?= e($edit['FIRSTNAME'] ?? '') ?>" required></div>
                <div class="field"><label>Middle name</label><input class="input" name="middlename"
                        value="<?= e($edit['MIDDLENAME'] ?? '') ?>"></div>
                <div class="field"><label>Last name</label><input class="input" name="lastname"
                        value="<?= e($edit['LASTNAME'] ?? '') ?>" required></div>
                <div class="field full"><label>Photo</label><input class="input" type="file" name="photo"
                        accept="image/jpeg,image/png,image/webp"></div>
            </div>
            <div class="modal-actions"><button class="btn btn-light" type="button"
                    onclick="this.closest('dialog').close()">Cancel</button><button class="btn btn-primary">Save
                    instructor</button></div>
        </form>
    </div>
</dialog>
<div class="card">
    <div class="toolbar">
        <form class="search" method="get"><input class="input" name="q" value="<?= e($q) ?>"
                placeholder="Search instructors..."><button class="btn btn-light">Search</button></form><span
            class="note"><?= $result->num_rows ?> record(s)</span>
    </div>
    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th>Photo</th>
                    <th>ID</th>
                    <th>Salutation</th>
                    <th>First name</th>
                    <th>Middle name</th>
                    <th>Last name</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody><?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php if ($row['PHOTO']): ?><img src="../<?= e($row['PHOTO']) ?>" alt="" width="40" height="40"
                                    style="object-fit:cover;border-radius:50%"><?php endif; ?></td>
                        <td><?= $row['ID'] ?></td>
                        <td><?= e($row['SALUTATION']) ?></td>
                        <td><?= e($row['FIRSTNAME']) ?></td>
                        <td><?= e($row['MIDDLENAME']) ?></td>
                        <td><?= e($row['LASTNAME']) ?></td>
                        <td>
                            <div class="actions"><a class="small-btn edit" data-native-edit="true"
                                    href="?id=<?= $row['ID'] ?>">Edit</a><a class="small-btn delete"
                                    href="?delete=<?= $row['ID'] ?>">Delete</a></div>
                        </td>
                    </tr><?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>
<?php if ($edit): ?>
    <script>document.getElementById('instructor-create').showModal()</script><?php endif; ?>
<?php include __DIR__ . '/../includes/footer.php'; ?>