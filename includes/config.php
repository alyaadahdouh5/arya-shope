<?php
// تعريف الثوابت
define('SITE_NAME', 'Arya Shope');
define('SITE_URL', 'http://localhost/AryaStore/');
define('CURRENCY', '₪');

// بدء الجلسة
session_start();

// إعدادات الاتصال بقاعدة البيانات
$host = 'localhost';
$dbname = 'aryashope_db';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("❌ فشل الاتصال بقاعدة البيانات: " . $e->getMessage());
}
?>