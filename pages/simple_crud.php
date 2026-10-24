<?php
$id = (int) ($_GET['id'] ?? 0);
$action = $_POST['action'] ?? '';
$error = '';
if ($action === 'save') {
    $values = [];
    foreach ($fields as $column => $label)
        $values[] = trim($_POST[strtolower($column)] ?? '');
    if (in_array('', $values, true))
        $error = 'Please complete all fields.';
    elseif ($id > 0) {
        $sets = implode(',', array_map(fn($c) => "$c=?", array_keys($fields)));
        $stmt = $conn->prepare("UPDATE $table SET $sets WHERE ID=?");
        $types = str_repeat('s', count($values)) . 'i';
        $params = [...$values, $id];
        bindParams($stmt, $types, $params);
        if ($stmt->execute()) {
            flash('success', "$title updated.");
            redirect(basename($_SERVER['PHP_SELF']));
        } else
            $error = 'Update failed. Check duplicate values.';
    } else {
        $cols = implode(',', array_keys($fields));
        $qs = implode(',', array_fill(0, count($values), '?'));
        $stmt = $conn->prepare("INSERT INTO $table ($cols) VALUES ($qs)");
        bindParams($stmt, str_repeat('s', count($values)), $values);
        if ($stmt->execute()) {
            flash('success', "$title added.");
            redirect(basename($_SERVER['PHP_SELF']));
        } else
            $error = 'Add failed. Check duplicate values.';
    }
}
if (isset($_GET['delete'])) {
    $delete = (int) $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM $table WHERE ID=?");
    $stmt->bind_param('i', $delete);
    if ($stmt->execute())
        flash('success', "$title deleted.");
    else
        flash('error', 'Delete failed. This record may still be used by another table.');
    redirect(basename($_SERVER['PHP_SELF']));
}
$edit = null;
if ($id) {
    $stmt = $conn->prepare("SELECT * FROM $table WHERE ID=?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $edit = $stmt->get_result()->fetch_assoc();
}
$q = trim($_GET['q'] ?? '');
$where = '';
$params = [];
$types = '';
if ($q !== '') {
    $parts = [];
    foreach ($searchCols as $c) {
        $parts[] = "$c LIKE ?";
        $params[] = "%$q%";
        $types .= 's';
    }
    $where = 'WHERE ' . implode(' OR ', $parts);
}
if ($where) {
    $stmt = $conn->prepare("SELECT * FROM $table $where ORDER BY ID DESC");
    bindParams($stmt, $types, $params);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query("SELECT * FROM $table ORDER BY ID DESC");
}
include __DIR__ . '/../includes/header.php';
showFlash();
if ($error)
    echo '<div class="alert error">' . e($error) . '</div>';
?>
<div class="page-head">
    <div>
        <h1><?= e($title) ?></h1>
        <p>Search and manage <?= e(strtolower($title)) ?> records.</p>
    </div><?php if (!$edit): ?><button class="btn btn-primary" type="button"
            onclick="document.getElementById('record-form-modal').showModal()">Add <?= e($title) ?></button><?php endif; ?>
</div>
<dialog class="record-modal" id="record-form-modal">
    <div class="modal-head">
        <h2><?= $edit ? 'Edit ' . e(strtolower($title)) : 'Add ' . e(strtolower($title)) ?></h2><button
            class="modal-close" type="button" onclick="this.closest('dialog').close()">×</button>
    </div>
    <div class="modal-body">
        <form class="record-form" method="post" action="<?= $edit ? '?id=' . $id : '' ?>"><input type="hidden"
                name="action" value="save">
            <div class="form-grid"><?php foreach ($fields as $column => $label):
                $name = strtolower($column); ?>
                    <div class="field"><label><?= e($label) ?></label><input class="input" name="<?= e($name) ?>"
                            value="<?= e($edit[$column] ?? '') ?>" required></div><?php endforeach; ?>
            </div>
            <div class="form-actions modal-actions"><button class="btn btn-light" type="button"
                    onclick="this.closest('dialog').close()">Cancel</button><button
                    class="btn btn-primary"><?= $edit ? 'Save changes' : 'Add record' ?></button></div>
        </form>
    </div>
</dialog>
<?php if ($edit): ?>
    <script>document.getElementById('record-form-modal').showModal()</script><?php endif; ?>
<div class="card">
    <div class="toolbar">
        <form class="search" method="get"><input class="input" name="q" value="<?= e($q) ?>"
                placeholder="Search <?= e(strtolower($title)) ?>..."><button class="btn btn-light">Search</button>
        </form><span class="note"><?= $result->num_rows ?> record(s)</span>
    </div>
    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th><?php foreach ($fields as $label): ?>
                        <th><?= e($label) ?></th><?php endforeach; ?>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody><?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['ID'] ?></td><?php foreach (array_keys($fields) as $col): ?>
                            <td><?= e($row[$col]) ?></td><?php endforeach; ?>
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