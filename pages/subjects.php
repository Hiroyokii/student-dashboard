<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';
$pageTitle = 'Subjects';
$id = (int) ($_GET['id'] ?? 0);
$error = '';
$courses = $conn->query('SELECT ID,COURSE FROM tbl_course ORDER BY COURSE');
$years = $conn->query('SELECT ID,YEARLEVEL FROM tbl_yearlevel ORDER BY ID');
$terms = $conn->query('SELECT ID,TERM FROM tbl_term ORDER BY ID');
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save') {
    $code = trim($_POST['subjectcode'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $units = (int) ($_POST['units'] ?? 0);
    $course = (int) ($_POST['course_id'] ?? 0);
    $year = (int) ($_POST['yearlevel_id'] ?? 0);
    $term = (int) ($_POST['term_id'] ?? 0);
    if (!$code || !$subject || $units < 1 || !$course || !$year || !$term)
        $error = 'Please complete all subject fields.';
    elseif ($id) {
        $stmt = $conn->prepare('UPDATE tbl_subjects SET SUBJECTCODE=?,SUBJECT=?,UNITS=?,COURSE_ID=?,YEARLEVEL_ID=?,TERM_ID=? WHERE ID=?');
        $stmt->bind_param('ssiiiii', $code, $subject, $units, $course, $year, $term, $id);
        if ($stmt->execute()) {
            flash('success', 'Subject updated.');
            redirect('subjects.php');
        } else
            $error = 'Update failed. Check duplicate code or related records.';
    } else {
        $stmt = $conn->prepare('INSERT INTO tbl_subjects (SUBJECTCODE,SUBJECT,UNITS,COURSE_ID,YEARLEVEL_ID,TERM_ID) VALUES (?,?,?,?,?,?)');
        $stmt->bind_param('ssiiii', $code, $subject, $units, $course, $year, $term);
        if ($stmt->execute()) {
            flash('success', 'Subject added.');
            redirect('subjects.php');
        } else
            $error = 'Add failed. Check duplicate code or related records.';
    }
}
if (isset($_GET['delete'])) {
    $delete = (int) $_GET['delete'];
    $stmt = $conn->prepare('DELETE FROM tbl_subjects WHERE ID=?');
    $stmt->bind_param('i', $delete);
    if ($stmt->execute())
        flash('success', 'Subject deleted.');
    else
        flash('error', 'Delete failed.');
    redirect('subjects.php');
}
$edit = null;
if ($id) {
    $stmt = $conn->prepare('SELECT * FROM tbl_subjects WHERE ID=?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $edit = $stmt->get_result()->fetch_assoc();
}
$q = trim($_GET['q'] ?? '');
$like = "%$q%";
$stmt = $conn->prepare('SELECT s.*,c.COURSE,y.YEARLEVEL,t.TERM FROM tbl_subjects s JOIN tbl_course c ON c.ID=s.COURSE_ID JOIN tbl_yearlevel y ON y.ID=s.YEARLEVEL_ID JOIN tbl_term t ON t.ID=s.TERM_ID WHERE s.SUBJECTCODE LIKE ? OR s.SUBJECT LIKE ? OR c.COURSE LIKE ? OR y.YEARLEVEL LIKE ? OR t.TERM LIKE ? ORDER BY s.ID DESC');
$stmt->bind_param('sssss', $like, $like, $like, $like, $like);
$stmt->execute();
$result = $stmt->get_result();
include __DIR__ . '/../includes/header.php';
showFlash();
if ($error)
    echo '<div class="alert error">' . e($error) . '</div>';
?>
<div class="page-head">
    <div>
        <h1>Subjects</h1>
        <p>Manage subjects and their course, year level, and term.</p>
    </div><?php if (!$edit): ?><button class="btn btn-primary" type="button"
            onclick="document.getElementById('record-form-modal').showModal()">Add subject</button><?php endif; ?>
</div>
<dialog class="record-modal" id="record-form-modal">
    <div class="modal-head">
        <h2><?= $edit ? 'Edit subject' : 'Add subject' ?></h2><button class="modal-close" type="button"
            onclick="this.closest('dialog').close()">×</button>
    </div>
    <div class="modal-body">
        <form class="record-form" method="post" action="<?= $edit ? '?id=' . $id : '' ?>"><input type="hidden"
                name="action" value="save">
            <div class="form-grid">
                <div class="field"><label>Subject Code</label><input class="input" name="subjectcode"
                        value="<?= e($edit['SUBJECTCODE'] ?? '') ?>" required></div>
                <div class="field"><label>Subject</label><input class="input" name="subject"
                        value="<?= e($edit['SUBJECT'] ?? '') ?>" required></div>
                <div class="field"><label>Units</label><input class="input" type="number" min="1" name="units"
                        value="<?= e($edit['UNITS'] ?? 3) ?>" required></div>
                <div class="field"><label>Course</label><select class="select" name="course_id"
                        required><?php while ($r = $courses->fetch_assoc()): ?>
                            <option value="<?= $r['ID'] ?>" <?= $edit && $edit['COURSE_ID'] == $r['ID'] ? 'selected' : '' ?>>
                                <?= e($r['COURSE']) ?></option><?php endwhile; ?>
                    </select></div>
                <div class="field"><label>Year Level</label><select class="select" name="yearlevel_id"
                        required><?php while ($r = $years->fetch_assoc()): ?>
                            <option value="<?= $r['ID'] ?>" <?= $edit && $edit['YEARLEVEL_ID'] == $r['ID'] ? 'selected' : '' ?>>
                                <?= e($r['YEARLEVEL']) ?></option><?php endwhile; ?>
                    </select></div>
                <div class="field"><label>Term</label><select class="select" name="term_id"
                        required><?php while ($r = $terms->fetch_assoc()): ?>
                            <option value="<?= $r['ID'] ?>" <?= $edit && $edit['TERM_ID'] == $r['ID'] ? 'selected' : '' ?>>
                                <?= e($r['TERM']) ?></option><?php endwhile; ?>
                    </select></div>
            </div>
            <div class="form-actions modal-actions"><button class="btn btn-light" type="button"
                    onclick="this.closest('dialog').close()">Cancel</button><button
                    class="btn btn-primary"><?= $edit ? 'Save changes' : 'Add subject' ?></button></div>
        </form>
    </div>
</dialog>
<?php if ($edit): ?>
    <script>document.getElementById('record-form-modal').showModal()</script><?php endif; ?>
<div class="card">
    <div class="toolbar">
        <form class="search" method="get"><input class="input" name="q" value="<?= e($q) ?>"
                placeholder="Search subjects..."><button class="btn btn-light">Search</button></form><span
            class="note"><?= $result->num_rows ?> record(s)</span>
    </div>
    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Code</th>
                    <th>Subject</th>
                    <th>Units</th>
                    <th>Course</th>
                    <th>Year</th>
                    <th>Term</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody><?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['ID'] ?></td>
                        <td><?= e($row['SUBJECTCODE']) ?></td>
                        <td><?= e($row['SUBJECT']) ?></td>
                        <td><?= $row['UNITS'] ?></td>
                        <td><?= e($row['COURSE']) ?></td>
                        <td><?= e($row['YEARLEVEL']) ?></td>
                        <td><?= e($row['TERM']) ?></td>
                        <td>
                            <div class="actions"><a class="small-btn edit" href="?id=<?= $row['ID'] ?>">Edit</a><a
                                    class="small-btn delete" href="?delete=<?= $row['ID'] ?>">Delete</a></div>
                        </td>
                    </tr><?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>