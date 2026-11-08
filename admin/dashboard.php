<?php
$page_title = 'لوحة التحكم - Arya Shope';
require_once '../includes/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

$total_products = 0;
$total_orders = 0;
$total_users = 0;
$unread_messages = 0;
$recent_orders = [];
$error = '';

try {
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM products");
    $total_products = $stmt->fetch()['count'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM orders");
    $total_orders = $stmt->fetch()['count'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users WHERE role = 'customer'");
    $total_users = $stmt->fetch()['count'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM contacts WHERE is_read = 0");
    $unread_messages = $stmt->fetch()['count'];
    
    $stmt = $pdo->query("
        SELECT o.*, u.full_name 
        FROM orders o 
        JOIN users u ON o.user_id = u.user_id 
        ORDER BY o.order_date DESC 
        LIMIT 5
    ");
    $recent_orders = $stmt->fetchAll();
    
} catch(PDOException $e) {
    $error = 'حدث خطأ في قاعدة البيانات';
}

require_once '../includes/header.php';
?>

<main class="container" style="padding: 30px 0;">
    <header style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; flex-wrap: wrap; gap: 15px;">
        <h1 style="color: var(--primary);">:لوحة التحكم</h1>
        <div style="display: flex; gap: 10px;">
            <span style="background: var(--primary-bg); padding: 8px 16px; border-radius: 20px; color: var(--gray);">
                👤 <?= htmlspecialchars($_SESSION['full_name']) ?>
            </span>
            <a href="../logout.php" class="btn btn-danger" style="padding: 8px 20px; font-size: 14px;">: خروج</a>
        </div>
    </header>

    <?php if (!empty($error)): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <section style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 40px;">
        <article style="background: linear-gradient(135deg, #FF8C00, #FFA500); color: white; padding: 25px; border-radius: var(--radius); box-shadow: var(--shadow);">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <p style="font-size: 14px; opacity: 0.8;">: المنتجات</p>
                    <h2 style="font-size: 32px; margin: 5px 0;"><?= $total_products ?></h2>
                </div>
                <span style="font-size: 40px;">📦</span>
            </div>
            <a href="products.php" style="color: white; font-size: 13px; opacity: 0.8;">إدارة المنتجات ←</a>
        </article>

        <article style="background: linear-gradient(135deg, #2E7D32, #4CAF50); color: white; padding: 25px; border-radius: var(--radius); box-shadow: var(--shadow);">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <p style="font-size: 14px; opacity: 0.8;">: الطلبات</p>
                    <h2 style="font-size: 32px; margin: 5px 0;"><?= $total_orders ?></h2>
                </div>
                <span style="font-size: 40px;">📦</span>
            </div>
            <a href="orders.php" style="color: white; font-size: 13px; opacity: 0.8;">إدارة الطلبات ←</a>
        </article>

        <article style="background: linear-gradient(135deg, #1565C0, #1976D2); color: white; padding: 25px; border-radius: var(--radius); box-shadow: var(--shadow);">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <p style="font-size: 14px; opacity: 0.8;">:المستخدمين</p>
                    <h2 style="font-size: 32px; margin: 5px 0;"><?= $total_users ?></h2>
                </div>
                <span style="font-size: 40px;">👥</span>
            </div>
            <a href="users.php" style="color: white; font-size: 13px; opacity: 0.8;">إدارة المستخدمين ←</a>
        </article>

        <article style="background: linear-gradient(135deg, #C62828, #D32F2F); color: white; padding: 25px; border-radius: var(--radius); box-shadow: var(--shadow);">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <p style="font-size: 14px; opacity: 0.8;">:رسائل غير مقروءة</p>
                    <h2 style="font-size: 32px; margin: 5px 0;"><?= $unread_messages ?></h2>
                </div>
                <span style="font-size: 40px;">💬</span>
            </div>
            <a href="messages.php" style="color: white; font-size: 13px; opacity: 0.8;">عرض الرسائل ←</a>
        </article>
    </section>

    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 30px;">
        <section style="background: white; padding: 20px; border-radius: var(--radius); border: 1px solid var(--border-color);">
            <h2 style="margin-bottom: 15px; font-size: 1.17em;">📋 أحدث الطلبات</h2>
            <?php if (empty($recent_orders)): ?>
                <p style="color: var(--gray);"> السوق نايم:</p>
            <?php else: ?>
                <div style="overflow-x: auto;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="border-bottom: 2px solid #f0f0f0;">
                                <th style="padding: 10px; text-align: right;">#</th>
                                <th style="padding: 10px; text-align: right;">العميل</th>
                                <th style="padding: 10px; text-align: right;">المبلغ</th>
                                <th style="padding: 10px; text-align: right;">الحالة</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_orders as $order): ?>
                                <tr style="border-bottom: 1px solid #f0f0f0;">
                                    <td style="padding: 10px;">#<?= str_pad($order['order_id'], 6, '0', STR_PAD_LEFT) ?></td>
                                    <td style="padding: 10px;"><?= htmlspecialchars($order['full_name']) ?></td>
                                    <td style="padding: 10px; font-weight: 700; color: var(--primary);">
                                        ₪<?= number_format($order['total_amount'], 2) ?>
                                    </td>
                                    <td style="padding: 10px;">
                                        <span style="padding: 3px 12px; border-radius: 20px; font-size: 12px; 
                                            <?php 
                                                switch($order['status']) {
                                                    case 'pending': echo 'background: #fff3e0; color: #e65100;'; break;
                                                    case 'paid': echo 'background: #e3f2fd; color: #0d47a1;'; break;
                                                    case 'shipped': echo 'background: #e8f5e9; color: #1b5e20;'; break;
                                                    case 'delivered': echo 'background: #e8f5e9; color: #1b5e20;'; break;
                                                    case 'cancelled': echo 'background: #ffebee; color: #b71c1c;'; break;
                                                }
                                            ?>">
                                            <?php
                                                switch($order['status']) {
                                                    case 'pending': echo '⏳ قيد الانتظار'; break;
                                                    case 'paid': echo '✅ تم الدفع'; break;
                                                    case 'shipped': echo '🚚 تم الشحن'; break;
                                                    case 'delivered': echo '📦 تم التوصيل'; break;
                                                    case 'cancelled': echo '❌ ملغي'; break;
                                                }
                                            ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div style="margin-top: 15px; text-align: left;">
                    <a href="orders.php" class="btn btn-outline" style="padding: 6px 20px; font-size: 13px;">عرض الكل ←</a>
                </div>
            <?php endif; ?>
        </section>

        <nav style="background: white; padding: 20px; border-radius: var(--radius); border: 1px solid var(--border-color);">
            <h2 style="margin-bottom: 15px; font-size: 1.17em;"> روابط سريعة</h2>
            <div style="display: flex; flex-direction: column; gap: 10px;">
                <a href="products.php" class="btn btn-primary" style="text-align: center;">🛍️ إدارة المنتجات</a>
                <a href="orders.php" class="btn btn-secondary" style="text-align: center;">📋 إدارة الطلبات</a>
                <a href="users.php" class="btn btn-outline" style="text-align: center;">👤 إدارة المستخدمين</a>
                <a href="messages.php" class="btn btn-outline" style="text-align: center;">✉️ إدارة الرسائل</a>
                <a href="../index.php" class="btn btn-outline" style="text-align: center;">🏠 العودة للموقع</a>
            </div>
        </nav>
    </div>
</main>

<?php require_once '../includes/footer.php'; ?>