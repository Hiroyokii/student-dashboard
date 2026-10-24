<?php
require_once __DIR__ . '/helpers.php';

// Works for both root pages (dashboard.php) and pages inside /pages.
$inPages = basename(dirname($_SERVER['SCRIPT_NAME'])) === 'pages';
$base = $inPages ? '../' : '';
$current = basename($_SERVER['PHP_SELF']);
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle ?? 'Student Management') ?></title>
    <link rel="stylesheet" href="<?= $base ?>assets/style.css">
</head>

<body>
    <header class="topbar">
        <a class="brand" href="<?= $base ?>dashboard.php">
            <span class="brand-dot"></span>
            Student Manager
        </a>

        <div class="userbox">
            <button class="profile-trigger" type="button" aria-label="Edit your profile" onclick="document.getElementById('profile-modal').showModal()">
                <?php if (!empty($_SESSION['image_path'])): ?>
                    <img class="user-avatar" src="<?= $base . e($_SESSION['image_path']) ?>" alt="">
                <?php else: ?>
                    <span class="profile-placeholder" aria-hidden="true"><?= e(strtoupper(substr($_SESSION['fullname'] ?? $_SESSION['username'] ?? 'U', 0, 1))) ?></span>
                <?php endif; ?>
                <span><?= e($_SESSION['fullname'] ?? $_SESSION['username'] ?? '') ?></span>
            </button>
            <a href="<?= $base ?>logout.php" class="logout-link">Log out</a>
        </div>

        <dialog class="record-modal profile-modal" id="profile-modal">
            <div class="modal-head"><h2>Edit your profile</h2><button class="modal-close" type="button" aria-label="Close" onclick="this.closest('dialog').close()">×</button></div>
            <div class="modal-body">
                <form class="record-form" method="post" action="<?= $base ?>profile.php" enctype="multipart/form-data">
                    <input type="hidden" name="return_to" value="<?= e($_SERVER['REQUEST_URI'] ?? ($base . 'dashboard.php')) ?>">
                    <div class="form-grid">
                        <div class="field"><label>First name</label><input class="input" name="firstname" value="<?= e($_SESSION['first_name'] ?? '') ?>" required></div>
                        <div class="field"><label>Last name</label><input class="input" name="lastname" value="<?= e($_SESSION['last_name'] ?? '') ?>" required></div>
                        <div class="field"><label>Email</label><input class="input" type="email" name="email" value="<?= e($_SESSION['email'] ?? '') ?>" required></div>
                        <div class="field"><label>Username</label><input class="input" name="username" value="<?= e($_SESSION['username'] ?? '') ?>" required></div>
                        <div class="field"><label>Profile picture</label><input class="input" type="file" name="imageUpload" accept="image/*"><small class="note">Leave empty to keep your current picture.</small></div>
                        <div class="field"><label>New password</label><input class="input" type="password" name="password" minlength="6" autocomplete="new-password"><small class="note">Leave empty to keep your current password.</small></div>
                    </div>
                    <div class="form-actions modal-actions"><button class="btn btn-light" type="button" onclick="this.closest('dialog').close()">Cancel</button><button class="btn btn-primary">Save profile</button></div>
                </form>
            </div>
        </dialog>
    </header>

    <div class="layout">
        <aside class="sidebar">
            <a href="<?= $base ?>dashboard.php"
                class="nav-item <?= $current === 'dashboard.php' ? 'active' : '' ?>">Dashboard</a>

            <div class="nav-label">Records</div>

            <?php
            $records = [
                'subjects.php' => 'Subjects',
                'term.php' => 'Term',
                'instructor.php' => 'Instructor',
                'schoolyear.php' => 'School Year',
                'section.php' => 'Section',
                'yearlevel.php' => 'Year Level',
                'course.php' => 'Course',
                'users.php' => 'Users',
                'usertype.php' => 'User Type'
            ];
            foreach ($records as $file => $label):
                $rolePages = ['Admin' => '*', 'Registrar' => ['subjects.php', 'term.php', 'instructor.php', 'schoolyear.php'], 'Dean' => ['subjects.php', 'section.php', 'instructor.php', 'yearlevel.php'], 'Instructor' => ['subjects.php', 'section.php'], 'Student' => ['subjects.php']];
                if (($rolePages[$role] ?? []) !== '*' && !in_array($file, $rolePages[$role] ?? [], true))
                    continue;
                $href = $inPages ? $file : 'pages/' . $file;
                ?>
                <a href="<?= e($href) ?>" class="nav-item <?= $current === $file ? 'active' : '' ?>"><?= e($label) ?></a>
            <?php endforeach; ?>
        </aside>

        <main class="content">