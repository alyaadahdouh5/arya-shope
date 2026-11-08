<?php
$page_title = 'التفاصيل الحلوة - Arya Shope';
require_once 'includes/config.php';
require_once 'includes/header.php';

$product_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($product_id === 0) {
    header('Location: products.php');
    exit;
}

try {
    $stmt = $pdo->prepare("
        SELECT p.*, c.name as category_name 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.category_id 
        WHERE p.product_id = ?
    ");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch();

    if (!$product) {
        header('Location: products.php');
        exit;
    }
} catch (PDOException $e) {
    header('Location: products.php');
    exit;
}
?>

<main class="container" style="padding: 30px 0;">
    <nav aria-label="التنقل">
        <a href="products.php" class="btn btn-outline" style="margin-bottom: 20px;">المعرض بحاله:</a>
    </nav>

    <section
        style="display: grid; grid-template-columns: 1fr 1fr; gap: 40px; background: white; padding: 30px; border-radius: var(--radius); box-shadow: var(--shadow);">

        <figure style="margin: 0;">
            <?php if ($product['image_url']): ?>
                <img src="<?= htmlspecialchars($product['image_url']) ?>" alt="<?= htmlspecialchars($product['name']) ?>"
                    style="width: 100%; height: 400px; object-fit: cover; border-radius: var(--radius);"
                    onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22400%22 height=%22400%22%3E%3Crect fill=%22%23f0f0f0%22 width=%22400%22 height=%22400%22/%3E%3Ctext x=%2250%25%22 y=%2250%25%22 text-anchor=%22middle%22 dy=%22.3em%22 font-size=%2264%22%3E👕%3C/text%3E%3C/svg%3E';">
            <?php else: ?>
                <div
                    style="width: 100%; height: 400px; background: var(--light-gray); display: flex; align-items: center; justify-content: center; font-size: 80px; border-radius: var(--radius);">
                    👕
                </div>
            <?php endif; ?>
        </figure>

        <article>
            <header>
                <h1 style="color: var(--dark); font-size: 32px;"><?= htmlspecialchars($product['name']) ?></h1>
                <p style="color: var(--gray); margin: 10px 0;"> @
                    <?= htmlspecialchars($product['category_name'] ?? 'غير مصنف') ?></p>
            </header>

            <p style="font-size: 28px; font-weight: 700; color: var(--primary); margin: 15px 0;">
                ₪<?= number_format($product['price'], 2) ?>
            </p>

            <p style="color: <?= $product['stock_quantity'] > 0 ? 'var(--secondary)' : '#d32f2f' ?>; font-weight: 600;">
                <?= $product['stock_quantity'] > 0 ? '✅ متوفر (' . (int) $product['stock_quantity'] . ' قطعة)' : '❌ غير متوفر' ?>
            </p>

            <section
                style="margin: 20px 0; padding: 20px; background: var(--primary-bg); border-radius: var(--radius);">
                <h2 style="font-size: 1.17em; margin-bottom: 5px;">📝 الوصف</h2>
                <p style="color: var(--gray); line-height: 1.8;">
                    <?= nl2br(htmlspecialchars($product['description'] ?? 'تواصل معنا من خلال الصفحة لتفاصيل اكتر')) ?>
                </p>
            </section>

            <?php if ($product['stock_quantity'] > 0): ?>
                <form id="addToCartForm" style="margin: 20px 0;">
                    <fieldset
                        style="border: none; padding: 0; margin: 0; display: flex; align-items: center; gap: 15px; flex-wrap: wrap;">
                        <label for="quantity" style="font-weight: 600;">الكمية:</label>
                        <input type="number" id="quantity" name="quantity" value="1" min="1"
                            max="<?= (int) $product['stock_quantity'] ?>"
                            style="width: 80px; padding: 10px; border: 2px solid #e0e0e0; border-radius: 8px; text-align: center;">

                        <button type="button" class="btn btn-secondary add-to-cart"
                            data-product-id="<?= (int) $product['product_id'] ?>"
                            style="padding: 12px 40px; font-size: 18px;">
                            عبي الكيس يمعلم
                        </button>
                    </fieldset>
                </form>
            <?php else: ?>
                <div class="alert alert-error">
                    كان عندنا والله جبر, استنى فتحة المعبر
                </div>
            <?php endif; ?>

            <section style="margin-top: 30px; display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                <article style="background: var(--primary-bg); padding: 12px; border-radius: 8px; text-align: center;">
                    <span style="color: var(--secondary);">🚚</span>
                    <p style="font-size: 13px; margin-top: 5px;">توصيل خلا 2 او 3 ايام</p>
                </article>
                <article style="background: var(--primary-bg); padding: 12px; border-radius: 8px; text-align: center;">
                    <span style="color: var(--secondary);">🔄</span>
                    <p style="font-size: 13px; margin-top: 5px;">التبديل بنفس اللحظة:</p>
                </article>
            </section>
        </article>
    </section>

    <?php
    try {
        $stmt = $pdo->prepare("
            SELECT * FROM products 
            WHERE category_id = ? AND product_id != ? 
            LIMIT 4
        ");
        $stmt->execute([$product['category_id'], $product['product_id']]);
        $similar_products = $stmt->fetchAll();
    } catch (PDOException $e) {
        $similar_products = [];
    }
    ?>

    <?php if (!empty($similar_products)): ?>
        <section style="margin-top: 40px;">
            <h2 style="color: var(--primary); margin-bottom: 20px;">حاجات ممكن تلفت انتباهك كمان:</h2>
            <div class="products-grid">
                <?php foreach ($similar_products as $similar): ?>
                    <article class="product-card fade-in">
                        <figure style="margin: 0;">
                            <?php if ($similar['image_url']): ?>
                                <img src="<?= htmlspecialchars($similar['image_url']) ?>"
                                    alt="<?= htmlspecialchars($similar['name']) ?>" class="product-image"
                                    onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                <div class="product-image-placeholder" style="display: none;">👕</div>
                            <?php else: ?>
                                <div class="product-image-placeholder">👕</div>
                            <?php endif; ?>
                        </figure>
                        <div class="product-info">
                            <h3 class="product-name"><?= htmlspecialchars($similar['name']) ?></h3>
                            <p class="product-price">₪<?= number_format($similar['price'], 2) ?></p>
                            <nav class="product-actions" aria-label="خيارات المنتج">
                                <a href="product_detail.php?id=<?= (int) $similar['product_id'] ?>" class="btn btn-outline"
                                    style="flex: 1;">التفاصيل الحلوة</a>
                            </nav>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>
</main>

<?php require_once 'includes/footer.php'; ?>