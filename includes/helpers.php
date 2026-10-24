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

function saveMediaUpload(array $upload, string $prefix, array $allowedMimeTypes, string $localName = ''): array
{
    if (($upload['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return ['path' => null, 'error' => ''];
    }
    if (($upload['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK || !is_uploaded_file($upload['tmp_name'] ?? '')) {
        return ['path' => null, 'error' => 'The image could not be uploaded.'];
    }
    if (($upload['size'] ?? 0) > 5 * 1024 * 1024) {
        return ['path' => null, 'error' => 'The image must be 5 MB or smaller.'];
    }
    $imageInfo = @getimagesize($upload['tmp_name']);
    if ($imageInfo === false || !in_array($imageInfo['mime'] ?? '', $allowedMimeTypes, true)) {
        return ['path' => null, 'error' => 'Please upload a valid image file.'];
    }

    if (getenv('MEDIA_DRIVER') !== 'database') {
        $extension = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif'][$imageInfo['mime']];
        if ($prefix === 'user_') {
            $targetDir = dirname(__DIR__) . '/images/users';
            $safeName = preg_replace('/[^A-Za-z0-9._-]/', '_', $localName);
            $filename = $safeName . '.JPG';
            $relativePath = 'images/users/' . $filename;
        } else {
            $targetDir = dirname(__DIR__) . '/assets/uploads';
            $filename = 'instructor_' . bin2hex(random_bytes(8)) . '.' . $extension;
            $relativePath = 'assets/uploads/' . $filename;
        }
        if (!is_dir($targetDir) && !mkdir($targetDir, 0755, true) && !is_dir($targetDir)) {
            return ['path' => null, 'error' => 'The image folder could not be created.'];
        }
        $targetPath = $targetDir . '/' . $filename;
        if ($prefix === 'user_' && function_exists('imagecreatefromstring') && function_exists('imagejpeg')) {
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
        return ['path' => $relativePath, 'error' => ''];
    }

    $content = file_get_contents($upload['tmp_name']);
    $conn = $GLOBALS['conn'] ?? null;
    if ($content === false || !($conn instanceof mysqli)) {
        return ['path' => null, 'error' => 'The image could not be saved.'];
    }

    $key = $prefix . bin2hex(random_bytes(18));
    $mime = $imageInfo['mime'];
    $stmt = $conn->prepare('INSERT INTO app_media (media_key,mime_type,file_data) VALUES (?,?,?)');
    if (!$stmt) {
        return ['path' => null, 'error' => 'The image could not be saved.'];
    }
    $blob = null;
    $stmt->bind_param('ssb', $key, $mime, $blob);
    $stmt->send_long_data(2, $content);
    if (!$stmt->execute()) {
        return ['path' => null, 'error' => 'The image could not be saved.'];
    }
    return ['path' => 'media.php?key=' . rawurlencode($key), 'error' => ''];
}

function saveUserImage(string $username, string $field = 'imageUpload'): array
{
    if (!isset($_FILES[$field])) return ['path' => null, 'error' => ''];
    return saveMediaUpload($_FILES[$field], 'user_', ['image/jpeg', 'image/png', 'image/webp', 'image/gif'], $username);
}
