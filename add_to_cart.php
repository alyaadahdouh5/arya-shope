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

if (!isset($_POST['product_id']) || !isset($_POST['quantity'])) {
    echo json_encode(['success' => false, 'message' => 'بيانات غير مكتملة']);
    exit;
}

$user_id = (int) $_SESSION['user_id'];
$product_id = (int) $_POST['product_id'];
$quantity = (int) $_POST['quantity'];

if ($quantity < 1) {
    echo json_encode(['success' => false, 'message' => 'الكمية يجب أن تكون أكبر من 0']);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT stock_quantity FROM products WHERE product_id = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch();

    if (!$product) {
        echo json_encode(['success' => false, 'message' => 'المنتج غير موجود']);
        exit;
    }

    $stmt = $pdo->prepare("SELECT * FROM cart_items WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$user_id, $product_id]);
    $existing = $stmt->fetch();

    if ($existing) {
        $new_quantity = $existing['quantity'] + $quantity;

        if ($new_quantity > $product['stock_quantity']) {
            echo json_encode(['success' => false, 'message' => 'الكمية الإجمالية المطلوبة تتجاوز الكمية المتوفرة في المخزون']);
            exit;
        }

        $stmt = $pdo->prepare("UPDATE cart_items SET quantity = ? WHERE cart_id = ?");
        $stmt->execute([$new_quantity, $existing['cart_id']]);
    } else {
        if ($product['stock_quantity'] < $quantity) {
            echo json_encode(['success' => false, 'message' => 'الكمية المطلوبة غير متوفرة']);
            exit;
        }

        $stmt = $pdo->prepare("INSERT INTO cart_items (user_id, product_id, quantity) VALUES (?, ?, ?)");
        $stmt->execute([$user_id, $product_id, $quantity]);
    }

    echo json_encode(['success' => true, 'message' => 'تم إضافة المنتج إلى السلة']);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'حدث خطأ في قاعدة البيانات']);
}
?>