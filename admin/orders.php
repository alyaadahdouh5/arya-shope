<?php
$page_title = 'إدارة الطلبات - Arya Shope';
require_once '../includes/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('❌ خطأ أمني (CSRF)');
    }

    $order_id = (int)$_POST['order_id'];
    $status = $_POST['status'] ?? 'pending';
    
    $allowed_statuses = ['pending', 'paid', 'shipped', 'delivered', 'cancelled'];
    if (!in_array($status, $allowed_statuses)) {
        $status = 'pending';
    }
    
    try {
        $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE order_id = ?");
        $stmt->execute([$status, $order_id]);
        $success = '✅ تم تحديث حالة الطلب';
    } catch(PDOException $e) {
        $error = '❌ حدث خطأ أثناء التحديث';
    }
}

if (isset($_GET['delete'])) {
    $order_id = (int)$_GET['delete'];
    
    try {
        $pdo->beginTransaction();
        
        $stmt = $pdo->prepare("DELETE FROM order_items WHERE order_id = ?");
        $stmt->execute([$order_id]);
        
        $stmt = $pdo->prepare("DELETE FROM orders WHERE order_id = ?");
        $stmt->execute([$order_id]);
        
        $pdo->commit();
        $success = '✅ تم حذف الطلب بنجاح';
    } catch(PDOException $e) {
        $pdo->rollBack();
        $error = '❌ لا يمكن حذف الطلب';
    }
}

if (isset($_POST['delete_completed'])) {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('❌ خطأ أمني (CSRF)');
    }

    try {
        $pdo->beginTransaction();
        
        $stmt = $pdo->prepare("
            DELETE oi FROM order_items oi 
            JOIN orders o ON oi.order_id = o.order_id 
            WHERE o.status = 'delivered'
        ");
        $stmt->execute();
        
        $stmt = $pdo->prepare("DELETE FROM orders WHERE status = 'delivered'");
        $stmt->execute();
        
        $pdo->commit();
        $success = '✅ تم حذف جميع الطلبات المكتملة بنجاح';
    } catch(PDOException $e) {
        $pdo->rollBack();
        $error = '❌ حدث خطأ أثناء الحذف';
    }
}

if (isset($_POST['delete_cancelled'])) {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('❌ خطأ أمني (CSRF)');
    }

    try {
        $pdo->beginTransaction();
        
        $stmt = $pdo->prepare("
            DELETE oi FROM order_items oi 
            JOIN orders o ON oi.order_id = o.order_id 
            WHERE o.status = 'cancelled'
        ");
        $stmt->execute();
        
        $stmt = $pdo->prepare("DELETE FROM orders WHERE status = 'cancelled'");
        $stmt->execute();
        
        $pdo->commit();
        $success = '✅ تم حذف جميع الطلبات الملغاة بنجاح';
    } catch(PDOException $e) {
        $pdo->rollBack();
        $error = '❌ حدث خطأ أثناء الحذف';
    }
}

try {
    $stmt = $pdo->query("
        SELECT o.*, u.full_name, u.email 
        FROM orders o 
        JOIN users u ON o.user_id = u.user_id 
        ORDER BY o.order_date DESC
    ");
    $orders = $stmt->fetchAll();
} catch(PDOException $e) {
    $orders = [];
}

require_once '../includes/header.php';
?>

<main class="container" style="padding: 30px 0;">
    <header style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; flex-wrap: wrap; gap: 15px;">
        <h1 style="color: var(--primary);">📋 إدارة الطلبات</h1>
        <a href="dashboard.php" class="btn btn-outline" style="padding: 8px 20px;">← العودة للوحة التحكم</a>
    </header>

    <?php if ($error): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <section style="margin-bottom: 20px; display: flex; gap: 10px; flex-wrap: wrap;">
        <form method="POST" action="" onsubmit="return confirm('هل أنت متأكد من حذف جميع الطلبات المكتملة؟');">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
            <input type="hidden" name="delete_completed" value="1">
            <button type="submit" class="btn btn-danger" style="padding: 8px 20px;">
                🗑️ حذف الطلبات المكتملة (delivered)
            </button>
        </form>
        <form method="POST" action="" onsubmit="return confirm('هل أنت متأكد من حذف جميع الطلبات الملغاة؟');">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
            <input type="hidden" name="delete_cancelled" value="1">
            <button type="submit" class="btn btn-danger" style="padding: 8px 20px; background: #c0392b;">
                🗑️ حذف الطلبات الملغاة (cancelled)
            </button>
        </form>
    </section>

    <?php if (empty($orders)): ?>
        <div class="alert alert-info">لا توجد طلبات حالياً</div>
    <?php else: ?>
        <section style="overflow-x: auto; background: white; border-radius: var(--radius); border: 1px solid var(--border-color); padding: 20px;">
            <table style="width: 100%; border-collapse: collapse; font-size: 14px;">
                <thead>
                    <tr style="border-bottom: 2px solid #f0f0f0;">
                        <th style="padding: 10px; text-align: right;">#</th>
                        <th style="padding: 10px; text-align: right;">العميل</th>
                        <th style="padding: 10px; text-align: right;">البريد الإلكتروني</th>
                        <th style="padding: 10px; text-align: right;">التاريخ</th>
                        <th style="padding: 10px; text-align: right;">المبلغ</th>
                        <th style="padding: 10px; text-align: right;">الحالة</th>
                        <th style="padding: 10px; text-align: center;">الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                        <tr style="border-bottom: 1px solid #f0f0f0;">
                            <td style="padding: 10px;">#<?= str_pad($order['order_id'], 6, '0', STR_PAD_LEFT) ?></td>
                            <td style="padding: 10px;"><?= htmlspecialchars($order['full_name']) ?></td>
                            <td style="padding: 10px;"><?= htmlspecialchars($order['email']) ?></td>
                            <td style="padding: 10px;"><?= date('Y-m-d', strtotime($order['order_date'])) ?></td>
                            <td style="padding: 10px; font-weight: 700; color: var(--primary);">
                                ₪<?= number_format($order['total_amount'], 2) ?>
                            </td>
                            <td style="padding: 10px;">
                                <form method="POST" action="" style="display: flex; gap: 5px; align-items: center;">
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                                    <input type="hidden" name="order_id" value="<?= $order['order_id'] ?>">
                                    <select name="status" style="padding: 5px 10px; border-radius: 6px; border: 1px solid #ddd;">
                                        <option value="pending" <?= $order['status'] == 'pending' ? 'selected' : '' ?>>⏳ قيد الانتظار</option>
                                        <option value="paid" <?= $order['status'] == 'paid' ? 'selected' : '' ?>>✅ تم الدفع</option>
                                        <option value="shipped" <?= $order['status'] == 'shipped' ? 'selected' : '' ?>>🚚 تم الشحن</option>
                                        <option value="delivered" <?= $order['status'] == 'delivered' ? 'selected' : '' ?>>📦 تم التوصيل</option>
                                        <option value="cancelled" <?= $order['status'] == 'cancelled' ? 'selected' : '' ?>>❌ ملغي</option>
                                    </select>
                                    <button type="submit" name="update_status" class="btn btn-secondary" style="padding: 4px 12px; font-size: 12px;">تحديث</button>
                                </form>
                            </td>
                            <td style="padding: 10px; text-align: center;">
                                <nav style="display: flex; gap: 5px; justify-content: center;">
                                    <a href="../order_detail.php?id=<?= $order['order_id'] ?>" class="btn btn-outline" style="padding: 4px 12px; font-size: 12px;" target="_blank">👁️</a>
                                    <a href="?delete=<?= $order['order_id'] ?>" class="btn btn-danger" style="padding: 4px 12px; font-size: 12px;" onclick="return confirm('هل أنت متأكد من حذف هذا الطلب؟')">🗑️</a>
                                </nav>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>
    <?php endif; ?>
</main>

<?php require_once '../includes/footer.php'; ?>