<?php
$page_title = 'سلة التسوق - Arya Shope';
require_once 'includes/config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php?redirect=cart');
    exit;
}

$user_id = (int) $_SESSION['user_id'];
$cart_items = [];
$total = 0;
$total_quantity = 0;

try {
    $stmt = $pdo->prepare("
        SELECT c.cart_id, c.quantity, p.* 
        FROM cart_items c 
        JOIN products p ON c.product_id = p.product_id 
        WHERE c.user_id = ?
    ");
    $stmt->execute([$user_id]);
    $cart_items = $stmt->fetchAll();

    foreach ($cart_items as $item) {
        $total += $item['price'] * $item['quantity'];
        $total_quantity += $item['quantity'];
    }
} catch (PDOException $e) {
    $cart_items = [];
}

require_once 'includes/header.php';
?>

<main class="container" style="padding: 30px 0;">
    <h1 style="color: var(--primary); margin-bottom: 30px;">🛒 سلة التسوق</h1>

    <?php if (empty($cart_items)): ?>
        <section class="alert alert-info" style="text-align: center; padding: 40px;">
            <h2>😔 الكيس فاضي 😔</h2>
            <p style="margin: 15px 0;">ما عجبك اشي؟</p>
            <a href="products.php" class="btn btn-primary">لفة ع المنتجات؟</a>
        </section>
    <?php else: ?>

        <section>
            <?php foreach ($cart_items as $item): ?>
                <article class="cart-item" id="cart-item-<?= (int) $item['cart_id'] ?>">
                    <figure style="margin: 0;">
                        <?php if ($item['image_url']): ?>
                            <img src="<?= htmlspecialchars($item['image_url']) ?>" alt="<?= htmlspecialchars($item['name']) ?>"
                                style="width: 100px; height: 100px; object-fit: cover; border-radius: 8px;"
                                onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                            <div class="product-image-placeholder"
                                style="display: none; width: 100px; height: 100px; background: #f5f5f5; border-radius: 8px; align-items: center; justify-content: center; font-size: 40px;">
                                👕</div>
                        <?php else: ?>
                            <div
                                style="width: 100px; height: 100px; background: var(--light-gray); display: flex; align-items: center; justify-content: center; font-size: 40px; border-radius: 8px;">
                                👕
                            </div>
                        <?php endif; ?>
                    </figure>

                    <div class="cart-item-info">
                        <h3><?= htmlspecialchars($item['name']) ?></h3>
                        <p class="price">₪<?= number_format($item['price'], 2) ?></p>
                        <p style="font-size: 13px; color: var(--gray);">
                            المجموع: ₪<?= number_format($item['price'] * $item['quantity'], 2) ?>
                        </p>
                    </div>

                    <nav class="cart-item-actions" aria-label="خيارات المنتج">
                        <input type="number" class="cart-quantity" data-cart-id="<?= (int) $item['cart_id'] ?>"
                            value="<?= (int) $item['quantity'] ?>" min="1" max="<?= (int) $item['stock_quantity'] ?>"
                            aria-label="الكمية">

                        <button class="btn btn-danger" onclick="removeFromCart(<?= (int) $item['cart_id'] ?>)"
                            aria-label="حذف المنتج">
                            🗑️
                        </button>
                    </nav>
                </article>
            <?php endforeach; ?>
        </section>

        <aside class="cart-summary">
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
                <div>
                    <p style="font-size: 18px;">
                        🛍️ إجمالي القطع: <strong><?= $total_quantity ?></strong>
                    </p>
                    <p class="total">
                        💰 المجموع الكلي: ₪<?= number_format($total, 2) ?>
                    </p>
                </div>
                <nav style="display: flex; gap: 10px; flex-wrap: wrap;" aria-label="خيارات السلة">
                    <a href="products.php" class="btn btn-outline">حابب تاخد اشي ثاني؟..</a>
                    <a href="checkout.php" class="btn btn-primary">كل اشي تمام!</a>
                </nav>
            </div>
        </aside>
    <?php endif; ?>
</main>

<?php require_once 'includes/footer.php'; ?>