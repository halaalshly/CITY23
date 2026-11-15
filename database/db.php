<?php
mysqli_report(MYSQLI_REPORT_OFF);

$host     = getenv('DB_HOST');
$user     = getenv('DB_USER');
$password = getenv('DB_PASS');
$database = getenv('DB_NAME');
$port     = (int) getenv('DB_PORT');

$conn = mysqli_init();
mysqli_ssl_set($conn, null, null, "/etc/ssl/certs/ca-certificates.crt", null, null);

if (!mysqli_real_connect($conn, $host, $user, $password, $database, $port, null, MYSQLI_CLIENT_SSL)) {
    die("فشل الاتصال بقاعدة البيانات: " . mysqli_connect_error());
}

mysqli_set_charset($conn, "utf8mb4");
?>
