<?php
$host = getenv('DB_HOST') ?: 'localhost';
$db = getenv('DB_NAME') ?: 'db_4bsalibay';
$user = getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASSWORD') ?: '';
$port = (int) (getenv('DB_PORT') ?: 3306);

$conn = mysqli_init();
if ($conn === false) {
    http_response_code(500);
    exit('Database is temporarily unavailable.');
}

$caFile = getenv('DB_SSL_CA');
if ($caFile) {
    $conn->ssl_set(null, null, $caFile, null, null);
}

$flags = filter_var(getenv('DB_SSL') ?: false, FILTER_VALIDATE_BOOLEAN) ? MYSQLI_CLIENT_SSL : 0;
if (!$conn->real_connect($host, $user, $pass, $db, $port, null, $flags)) {
    error_log('Database connection failed: ' . $conn->connect_error);
    http_response_code(500);
    exit('Database is temporarily unavailable.');
}
$conn->set_charset('utf8mb4');
