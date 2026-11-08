<?php
$page_title = 'التسوق - Arya Shope';
require_once 'includes/config.php';
require_once 'includes/header.php';

try {
    $stmt = $pdo->prepare("
        SELECT p.*, c.name as category_name 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.category_id 
        ORDER BY p.product_id DESC 
        LIMIT 6
    ");
    $stmt->execute();
    $featured_products = $stmt->fetchAll();
} catch (PDOException $e) {
    $featured_products = [];
}
?>

<main class="container">

    <section class="hero fade-in">
        <h1>مرحباً بك في Arya Shope</h1>
        <p>عشان الشارع متعب ومكسر جبنا الك سوق ع النت</p>
        <a href="products.php" class="btn btn-primary">خدلك لفة</a>
    </section>

    <section aria-label="البحث" style="margin-bottom: 30px;">
        <form action="products.php" method="GET" style="display: flex; gap: 10px; max-width: 500px; margin: 0 auto;">
            <input type="text" name="search" placeholder="بتدور ع حاجة معينة؟"
                style="flex: 1; padding: 12px 20px; border: 2px solid var(--border-color); border-radius: 25px; font-size: 16px;">
            <button type="submit" class="btn btn-primary">بحث</button>
        </form>
    </section>

    <section>
        <header style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h2 style="color: var(--primary);">الطلب عالي والعميل غالي</h2>
            <a href="products.php" style="color: var(--primary); font-weight: 600;">عرض الكل ←</a>
        </header>

        <?php if (empty($featured_products)): ?>
            <div class="alert alert-info">
                <p>كان عندنا والله جبر, استنى فتحة المعبر</p>
            </div>
        <?php else: ?>
            <div class="products-grid">
                <?php foreach ($featured_products as $product): ?>
                    <article class="product-card fade-in">
                        <figure style="margin: 0;">
                            <?php if ($product['image_url']): ?>
                                <img src="<?= htmlspecialchars($product['image_url']) ?>"
                                    alt="<?= htmlspecialchars($product['name']) ?>" class="product-image"
                                    onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                <div class="product-image-placeholder" style="display: none;">👕</div>
                            <?php else: ?>
                                <div class="product-image-placeholder">👕</div>
                            <?php endif; ?>
                        </figure>

                        <div class="product-info">
                            <h3 class="product-name"><?= htmlspecialchars($product['name']) ?></h3>
                            <p class="product-category">📂 <?= htmlspecialchars($product['category_name'] ?? 'غير مصنف') ?></p>
                            <p class="product-price">₪<?= number_format($product['price'], 2) ?></p>

                            <nav class="product-actions" aria-label="خيارات المنتج">
                                <a href="product_detail.php?id=<?= (int) $product['product_id'] ?>" class="btn btn-outline"
                                    style="flex: 1;">التفاصيل</a>
                                <button class="btn btn-secondary add-to-cart"
                                    data-product-id="<?= (int) $product['product_id'] ?>" style="flex: 1;">
                                    عبي الكيس يمعلم
                                </button>
                            </nav>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>

    <section
        style="margin: 50px 0; display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
        <article
            style="background: white; padding: 25px; border-radius: var(--radius); text-align: center; border: 1px solid var(--border-color);">
            <div style="font-size: 40px; color: var(--secondary);">🚚</div>
            <h3>توصيل سريع</h3>
            <p style="color: var(--gray); font-size: 14px;">توصيل خلال 2-3 أيام</p>
        </article>

        <article
            style="background: white; padding: 25px; border-radius: var(--radius); text-align: center; border: 1px solid var(--border-color);">
            <div style="font-size: 40px; color: var(--secondary);">🔄</div>
            <h3>استرجاع</h3>
            <p style="color: var(--gray); font-size: 14px;">التبديل في نفس الوقت مش فاضي الك</p>
        </article>

        <article
            style="background: white; padding: 25px; border-radius: var(--radius); text-align: center; border: 1px solid var(--border-color);">
            <div style="font-size: 40px; color: var(--secondary);">💳</div>
            <h3>دفع تطبيق وكاش</h3>
            <p style="color: var(--gray); font-size: 14px;">بنقبل كاش وتطبيق</p>
        </article>

        <article
            style="background: white; padding: 25px; border-radius: var(--radius); text-align: center; border: 1px solid var(--border-color);">
            <div style="font-size: 40px; color: var(--secondary);">💎</div>
            <h3>الي عنا مش عند حد تاني</h3>
            <p style="color: var(--gray); font-size: 14px;">منتجات أصلية 100%</p>
        </article>
    </section>
</main>

<?php require_once 'includes/footer.php'; ?>