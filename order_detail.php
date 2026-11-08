<?php
$page_title = 'تفاصيل الطلب - Arya Shope';
require_once 'includes/config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php?redirect=order_detail.php?id=' . (int) ($_GET['id'] ?? 0));
    exit;
}

$order_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($order_id === 0) {
    header('Location: order_history.php');
    exit;
}

$user_id = (int) $_SESSION['user_id'];

try {
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE order_id = ? AND user_id = ?");
    $stmt->execute([$order_id, $user_id]);
    $order = $stmt->fetch();

    if (!$order) {
        header('Location: order_history.php');
        exit;
    }

    $stmt = $pdo->prepare("
        SELECT oi.*, p.name, p.image_url 
        FROM order_items oi 
        JOIN products p ON oi.product_id = p.product_id 
        WHERE oi.order_id = ?
    ");
    $stmt->execute([$order_id]);
    $items = $stmt->fetchAll();
} catch (PDOException $e) {
    header('Location: order_history.php');
    exit;
}

require_once 'includes/header.php';
?>

<main class="container">
    <header>
        <h1 style="color: var(--primary); margin: 30px 0 20px;">التفاصيل الحلوة:
            #<?= str_pad($order_id, 6, '0', STR_PAD_LEFT) ?></h1>
    </header>

    <nav aria-label="العودة">
        <a href="order_history.php" class="btn btn-outline" style="margin-bottom: 20px;">← العودة لتاريخ الطلبات</a>
    </nav>

    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 30px;">
        <section style="background: white; padding: 25px; border-radius: var(--radius); box-shadow: var(--shadow);">
            <h2 style="font-size: 1.17em; margin-bottom: 15px;">🛍️ المنتجات</h2>

            <?php foreach ($items as $item): ?>
                <div
                    style="display: flex; justify-content: space-between; padding: 12px 0; border-bottom: 1px solid var(--border-color);">
                    <div>
                        <strong><?= htmlspecialchars($item['name']) ?></strong>
                        <span style="color: var(--gray); font-size: 14px;">× <?= (int) $item['quantity'] ?></span>
                    </div>
                    <span style="font-weight: 600;">₪<?= number_format($item['unit_price'] * $item['quantity'], 2) ?></span>
                </div>
            <?php endforeach; ?>

            <footer
                style="display: flex; justify-content: space-between; padding: 15px 0; font-size: 20px; font-weight: 700; border-top: 2px solid var(--primary); margin-top: 10px;">
                <span>المجموع الكلي:</span>
                <span style="color: var(--primary);">₪<?= number_format($order['total_amount'], 2) ?></span>
            </footer>
        </section>

        <aside>
            <section
                style="background: white; padding: 25px; border-radius: var(--radius); box-shadow: var(--shadow); margin-bottom: 20px;">
                <h2 style="font-size: 1.17em; margin-bottom: 15px;">📋 معلومات الطلب</h2>
                <div style="margin: 15px 0;">
                    <p><strong>📅 التاريخ:</strong> <?= date('Y-m-d H:i', strtotime($order['order_date'])) ?></p>
                    <p><strong>💰 المجموع:</strong> ₪<?= number_format($order['total_amount'], 2) ?></p>
                    <p><strong>📦 حالة الطلب:</strong>
                        <?php
                        $status_labels = [
                            'pending' => '⏳ قيد الانتظار',
                            'paid' => '✅ مدفوع',
                            'shipped' => '🚚 تم الشحن',
                            'delivered' => '📦 تم التسليم',
                            'cancelled' => '❌ ملغي'
                        ];
                        $status_colors = [
                            'pending' => '#FF9800',
                            'paid' => '#4CAF50',
                            'shipped' => '#2196F3',
                            'delivered' => '#2E7D32',
                            'cancelled' => '#d32f2f'
                        ];
                        ?>
                        <span
                            style="background: <?= $status_colors[$order['status']] ?? '#999' ?>; color: white; padding: 4px 12px; border-radius: 12px; font-size: 14px;">
                            <?= $status_labels[$order['status']] ?? htmlspecialchars($order['status']) ?>
                        </span>
                    </p>
                </div>
            </section>

            <section style="background: white; padding: 25px; border-radius: var(--radius); box-shadow: var(--shadow);">
                <h2 style="font-size: 1.17em; margin-bottom: 15px;">📍 عنوان الشحن</h2>
                <div style="margin: 15px 0; padding: 15px; background: var(--primary-bg); border-radius: 8px;">
                    <p style="line-height: 1.8;"><?= nl2br(htmlspecialchars($order['shipping_address'])) ?></p>
                </div>
            </section>

            <?php if ($order['status'] == 'pending'): ?>
                <aside
                    style="background: #fff3e0; padding: 15px; border-radius: var(--radius); margin-top: 20px; border-right: 4px solid #FF9800;">
                    <p style="color: #e65100;">⏳ طلبك قيد المعالجة، سيتم التواصل معك قريباً</p>
                </aside>
            <?php endif; ?>
        </aside>
    </div>
</main>

<?php require_once 'includes/footer.php'; ?>