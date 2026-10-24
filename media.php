<?php
require_once __DIR__ . '/config/db.php';

$key = $_GET['key'] ?? '';
if (!is_string($key) || !preg_match('/^(user|instructor)_[a-f0-9]{36}$/', $key)) {
    http_response_code(404);
    exit;
}

$stmt = $conn->prepare('SELECT mime_type,file_data FROM app_media WHERE media_key=?');
$stmt->bind_param('s', $key);
$stmt->execute();
$stmt->bind_result($mimeType, $fileData);
if (!$stmt->fetch()) {
    http_response_code(404);
    exit;
}

if (!in_array($mimeType, ['image/jpeg', 'image/png', 'image/webp', 'image/gif'], true)) {
    http_response_code(415);
    exit;
}
header('Content-Type: ' . $mimeType);
header('X-Content-Type-Options: nosniff');
header('Cache-Control: public, max-age=86400');
echo $fileData;
