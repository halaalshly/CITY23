<?php
$host = getenv('DB_HOST') ?: 'gateway01.ap-northeast-1prod.aws.tidbcloud.com';
$user = getenv('DB_USER') ?: '26esEiSLiL9QND1root';
$password = getenv('DB_PASS') ?: 'siLV1LxFrR1e4PtE';
$database = getenv('DB_NAME') ?: 'city';
$port = getenv('DB_PORT') ?: 4000;

// الاتصال مع تحديد المنفذ (Port) السحابي
$conn = mysqli_init();

if (!mysqli_real_connect($conn, $host, $user, $password, $database, $port, NULL, MYSQLI_CLIENT_SSL)) {
    die("فشل الاتصال بقاعدة البيانات: " . mysqli_connect_error());
}

mysqli_set_charset($conn, "utf8mb4");
?>
