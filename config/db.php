<?php
$host = getenv('DB_HOST') ?: getenv('TIDB_HOST') ?: 'localhost';
$db = getenv('DB_NAME') ?: getenv('TIDB_DATABASE') ?: 'db_4bsalibay';
$user = getenv('DB_USER') ?: getenv('TIDB_USER') ?: 'root';
$pass = getenv('DB_PASSWORD') ?: getenv('TIDB_PASSWORD') ?: '';
$port = (int) (getenv('DB_PORT') ?: getenv('TIDB_PORT') ?: 3306);

$conn = mysqli_init();
if ($conn === false) {
    http_response_code(500);
    exit('Database is temporarily unavailable.');
}

$caFile = getenv('DB_SSL_CA') ?: (getenv('TIDB_HOST') ? '/etc/ssl/certs/ca-certificates.crt' : '');
if ($caFile) {
    $conn->ssl_set(null, null, $caFile, null, null);
}

$sslSetting = getenv('DB_SSL');
$useSsl = $sslSetting === false ? (bool) getenv('TIDB_HOST') : filter_var($sslSetting, FILTER_VALIDATE_BOOLEAN);
$flags = $useSsl ? MYSQLI_CLIENT_SSL : 0;
if (!$conn->real_connect($host, $user, $pass, $db, $port, null, $flags)) {
    error_log('Database connection failed: ' . $conn->connect_error);
    http_response_code(500);
    exit('Database is temporarily unavailable.');
}
$conn->set_charset('utf8mb4');
