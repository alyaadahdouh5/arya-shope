<?php
header('Content-Type: application/json; charset=utf-8');
require_once 'includes/config.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'الرجاء تسجيل الدخول أولاً']);
    exit;
}

if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    echo json_encode(['success' => false, 'message' => 'خطأ أمني (CSRF)، يرجى تحديث الصفحة']);
    exit;
}

if (!isset($_POST['cart_id'])) {
    echo json_encode(['success' => false, 'message' => 'بيانات غير مكتملة']);
    exit;
}

$cart_id = (int) $_POST['cart_id'];
$user_id = (int) $_SESSION['user_id'];

try {
    $stmt = $pdo->prepare("DELETE FROM cart_items WHERE cart_id = ? AND user_id = ?");
    $stmt->execute([$cart_id, $user_id]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => 'تم حذف المنتج من السلة']);
    } else {
        echo json_encode(['success' => false, 'message' => 'العنصر غير موجود']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'حدث خطأ في قاعدة البيانات']);
}
?>