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

if (!isset($_POST['cart_id']) || !isset($_POST['quantity'])) {
    echo json_encode(['success' => false, 'message' => 'بيانات غير مكتملة']);
    exit;
}

$cart_id = (int) $_POST['cart_id'];
$quantity = (int) $_POST['quantity'];
$user_id = (int) $_SESSION['user_id'];

if ($quantity < 1) {
    echo json_encode(['success' => false, 'message' => 'الكمية يجب أن تكون أكبر من 0']);
    exit;
}

try {
    $stmt = $pdo->prepare("
        SELECT c.*, p.stock_quantity 
        FROM cart_items c 
        JOIN products p ON c.product_id = p.product_id 
        WHERE c.cart_id = ? AND c.user_id = ?
    ");
    $stmt->execute([$cart_id, $user_id]);
    $item = $stmt->fetch();

    if (!$item) {
        echo json_encode(['success' => false, 'message' => 'العنصر غير موجود']);
        exit;
    }

    if ($item['stock_quantity'] < $quantity) {
        echo json_encode(['success' => false, 'message' => 'الكمية المطلوبة غير متوفرة']);
        exit;
    }

    $stmt = $pdo->prepare("UPDATE cart_items SET quantity = ? WHERE cart_id = ? AND user_id = ?");
    $stmt->execute([$quantity, $cart_id, $user_id]);

    echo json_encode(['success' => true, 'message' => 'تم تحديث السلة']);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'حدث خطأ في قاعدة البيانات']);
}
?>