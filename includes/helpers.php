<?php
function e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function redirect(string $url): never
{
    header('Location: ' . $url);
    exit;
}

function flash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function showFlash(): void
{
    if (!empty($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        echo '<div class="alert ' . e($flash['type']) . '">' . e($flash['message']) . '</div>';
    }
}

function bindParams(mysqli_stmt $stmt, string $types, array $params): void
{
    $refs = [$types];
    foreach ($params as $key => $value) {
        $refs[] = &$params[$key];
    }
    call_user_func_array([$stmt, 'bind_param'], $refs);
}

function saveUserImage(string $username, string $field = 'imageUpload'): array
{
    if (!isset($_FILES[$field]) || $_FILES[$field]['error'] === UPLOAD_ERR_NO_FILE) {
        return ['path' => null, 'error' => ''];
    }
    $upload = $_FILES[$field];
    if ($upload['error'] !== UPLOAD_ERR_OK || !is_uploaded_file($upload['tmp_name'])) {
        return ['path' => null, 'error' => 'The image could not be uploaded.'];
    }
    if ($upload['size'] > 5 * 1024 * 1024) {
        return ['path' => null, 'error' => 'The image must be 5 MB or smaller.'];
    }
    // getimagesize validates that the uploaded content is a supported image
    // without requiring the optional GD extension in XAMPP.
    if (@getimagesize($upload['tmp_name']) === false) {
        return ['path' => null, 'error' => 'Please upload a valid image file.'];
    }
    $targetDir = dirname(__DIR__) . '/images/users';
    if (!is_dir($targetDir) && !mkdir($targetDir, 0755, true) && !is_dir($targetDir)) {
        return ['path' => null, 'error' => 'The image folder could not be created.'];
    }
    $safeUsername = preg_replace('/[^A-Za-z0-9._-]/', '_', $username);
    $filename = $safeUsername . '.JPG';
    $targetPath = $targetDir . '/' . $filename;
    if (function_exists('imagecreatefromstring') && function_exists('imagejpeg')) {
        $imageData = file_get_contents($upload['tmp_name']);
        $image = $imageData === false ? false : @imagecreatefromstring($imageData);
        if ($image === false || !imagejpeg($image, $targetPath, 90)) {
            if ($image !== false) imagedestroy($image);
            return ['path' => null, 'error' => 'The image could not be saved.'];
        }
        imagedestroy($image);
    } elseif (!move_uploaded_file($upload['tmp_name'], $targetPath)) {
        return ['path' => null, 'error' => 'The image could not be saved.'];
    }
    return ['path' => 'images/users/' . $filename, 'error' => ''];
}
