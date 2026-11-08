<?php
$page_title = 'إتمام الطلب - Arya Shope';
require_once 'includes/config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php?redirect=checkout');
    exit;
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$user_id = (int) $_SESSION['user_id'];
$error = '';
$success = false;
$order_id = 0;

try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
} catch (PDOException $e) {
    $user = null;
}

try {
    $stmt = $pdo->prepare("
        SELECT c.*, p.name, p.price, p.image_url 
        FROM cart_items c 
        JOIN products p ON c.product_id = p.product_id 
        WHERE c.user_id = ?
    ");
    $stmt->execute([$user_id]);
    $cart_items = $stmt->fetchAll();
} catch (PDOException $e) {
    $cart_items = [];
}

$total = 0;
foreach ($cart_items as $item) {
    $total += $item['price'] * $item['quantity'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error = '❌ خطأ أمني (CSRF)، يرجى تحديث الصفحة والمحاولة مرة أخرى';
    } else {
        $shipping_address = trim($_POST['shipping_address'] ?? $user['address'] ?? '');

        if (empty($shipping_address)) {
            $error = '⚠️ الرجاء إدخال عنوان الشحن بالتفصيل';
        } elseif (empty($cart_items)) {
            $error = '⚠️ سلة التسوق فارغة';
        } else {
            try {
                $pdo->beginTransaction();

                $stmt = $pdo->prepare("
                    INSERT INTO orders (user_id, total_amount, shipping_address, status) 
                    VALUES (?, ?, ?, 'pending')
                ");
                $stmt->execute([$user_id, $total, $shipping_address]);
                $order_id = (int) $pdo->lastInsertId();

                $stmtItem = $pdo->prepare("
                    INSERT INTO order_items (order_id, product_id, quantity, unit_price) 
                    VALUES (?, ?, ?, ?)
                ");

                $stmtStock = $pdo->prepare("
                    UPDATE products 
                    SET stock_quantity = stock_quantity - ? 
                    WHERE product_id = ? AND stock_quantity >= ?
                ");

                foreach ($cart_items as $item) {
                    $stmtItem->execute([
                        $order_id,
                        $item['product_id'],
                        $item['quantity'],
                        $item['price']
                    ]);

                    $stmtStock->execute([
                        $item['quantity'],
                        $item['product_id'],
                        $item['quantity']
                    ]);

                    if ($stmtStock->rowCount() === 0) {
                        throw new Exception("الكمية المطلوبة من المنتج (" . htmlspecialchars($item['name']) . ") لم تعد متوفرة في المخزون.");
                    }
                }

                $stmt = $pdo->prepare("DELETE FROM cart_items WHERE user_id = ?");
                $stmt->execute([$user_id]);

                $pdo->commit();
                $success = true;

            } catch (Exception $e) {
                $pdo->rollBack();
                if ($e instanceof PDOException) {
                    $error = '❌ حدث خطأ في قاعدة البيانات، حاول مرة أخرى';
                } else {
                    $error = '❌ ' . $e->getMessage();
                }
            }
        }
    }
}

require_once 'includes/header.php';
?>

<main class="container" style="padding: 30px 0;">
    <header>
        <h1 style="color: var(--primary); margin-bottom: 30px;">🚚 إتمام الطلب</h1>
    </header>

    <?php if ($success): ?>
        <section class="alert alert-success" style="text-align: center; padding: 40px;">
            <h2 style="font-size: 48px;">🎉</h2>
            <h3>تم استلام طلبك بنجاح، انتظر تواصل من الإدارة...</h3>
            <p style="margin: 15px 0;">رقم الطلب: <strong>#<?= str_pad($order_id, 6, '0', STR_PAD_LEFT) ?></strong></p>
            <p style="color: var(--gray);">تم إرسال تأكيد الطلب إلى حسابك</p>
            <nav style="margin-top: 20px; display: flex; gap: 10px; justify-content: center; flex-wrap: wrap;"
                aria-label="خيارات الطلب">
                <a href="order_detail.php?id=<?= $order_id ?>" class="btn btn-primary">👁️ عرض الطلب</a>
                <a href="index.php" class="btn btn-outline">🏠 العودة للرئيسية</a>
            </nav>
        </section>
    <?php else: ?>
        <?php if ($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if (empty($cart_items)): ?>
            <section class="alert alert-info" style="text-align: center; padding: 40px;">
                <h3>🛒 سلة التسوق فارغة</h3>
                <a href="products.php" class="btn btn-primary" style="margin-top: 15px;">🛍️ التسوق الآن</a>
            </section>
        <?php else: ?>
            <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 30px;">
                <section
                    style="background: white; padding: 25px; border-radius: var(--radius); border: 1px solid var(--border-color);">
                    <h2 style="margin-bottom: 20px; font-size: 1.17em;">📍 معلومات الشحن</h2>
                    <form method="POST" action="">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

                        <fieldset class="form-group" style="border: none; padding: 0; margin: 0 0 18px 0;">
                            <label for="shipping_address">عنوان الشحن</label>
                            <textarea id="shipping_address" name="shipping_address" rows="3" required
                                placeholder="عنوانك بالتفصيل..."><?= htmlspecialchars($_POST['shipping_address'] ?? $user['address'] ?? '') ?></textarea>
                        </fieldset>

                        <button type="submit" name="place_order" class="btn btn-primary"
                            style="width: 100%; padding: 14px; font-size: 18px;">
                            تأكيد الطلب
                        </button>
                    </form>
                </section>

                <aside
                    style="background: white; padding: 25px; border-radius: var(--radius); border: 1px solid var(--border-color);">
                    <h2 style="margin-bottom: 20px; font-size: 1.17em;">📋 ملخص الطلب</h2>

                    <?php foreach ($cart_items as $item): ?>
                        <div
                            style="display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #f0f0f0;">
                            <span><?= htmlspecialchars($item['name']) ?> × <?= (int) $item['quantity'] ?></span>
                            <span>₪<?= number_format($item['price'] * $item['quantity'], 2) ?></span>
                        </div>
                    <?php endforeach; ?>

                    <div
                        style="display: flex; justify-content: space-between; padding: 15px 0; border-top: 2px solid var(--primary); margin-top: 10px;">
                        <strong style="font-size: 18px;">المجموع الكلي</strong>
                        <strong style="font-size: 22px; color: var(--primary);">₪<?= number_format($total, 2) ?></strong>
                    </div>
                </aside>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</main>

<?php require_once 'includes/footer.php'; ?>