<?php
$page_title = 'تاريخ الطلبات - Arya Shope';
require_once 'includes/config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php?redirect=order_history.php');
    exit;
}

$user_id = (int) $_SESSION['user_id'];

try {
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY order_date DESC");
    $stmt->execute([$user_id]);
    $orders = $stmt->fetchAll();
} catch (PDOException $e) {
    $orders = [];
}

require_once 'includes/header.php';
?>

<main class="container">
    <header>
        <h1 style="color: var(--primary); margin: 30px 0 20px;">📋 تاريخ الطلبات</h1>
    </header>

    <nav aria-label="العودة">
        <a href="profile.php" class="btn btn-outline" style="margin-bottom: 20px;">← العودة للملف الشخصي</a>
    </nav>

    <?php if (empty($orders)): ?>
        <section class="alert alert-info" style="text-align: center; padding: 40px;">
            <h2>📦 لا توجد طلبات</h2>
            <p>لم تقم بإجراء أي طلبات بعد</p>
            <a href="products.php" class="btn btn-primary" style="margin-top: 15px;">خدلك لفة:</a>
        </section>
    <?php else: ?>
        <section style="background: white; border-radius: var(--radius); box-shadow: var(--shadow); overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse;">
                <thead style="background: var(--primary); color: white;">
                    <tr>
                        <th style="padding: 12px; text-align: right;">#</th>
                        <th style="padding: 12px; text-align: right;">التاريخ</th>
                        <th style="padding: 12px; text-align: right;">المجموع</th>
                        <th style="padding: 12px; text-align: right;">الحالة</th>
                        <th style="padding: 12px; text-align: center;">الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
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
                        <tr style="border-bottom: 1px solid var(--border-color);">
                            <td style="padding: 12px; font-weight: 600;">
                                #<?= str_pad((int) $order['order_id'], 6, '0', STR_PAD_LEFT) ?></td>
                            <td style="padding: 12px;"><?= date('Y-m-d H:i', strtotime($order['order_date'])) ?></td>
                            <td style="padding: 12px; color: var(--primary); font-weight: 700;">
                                ₪<?= number_format($order['total_amount'], 2) ?></td>
                            <td style="padding: 12px;">
                                <span
                                    style="background: <?= $status_colors[$order['status']] ?? '#999' ?>; color: white; padding: 4px 12px; border-radius: 12px; font-size: 13px;">
                                    <?= $status_labels[$order['status']] ?? htmlspecialchars($order['status']) ?>
                                </span>
                            </td>
                            <td style="padding: 12px; text-align: center;">
                                <nav aria-label="إجراءات الطلب">
                                    <a href="order_detail.php?id=<?= (int) $order['order_id'] ?>" class="btn btn-outline"
                                        style="padding: 6px 16px; font-size: 13px;">التفاصيل</a>
                                </nav>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>
    <?php endif; ?>
</main>

<?php require_once 'includes/footer.php'; ?>