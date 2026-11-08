<?php
$page_title = 'جميع المنتجات - Arya Shope';
require_once 'includes/config.php';
require_once 'includes/header.php';

$category_filter = isset($_GET['category']) ? (int) $_GET['category'] : 0;
$search_filter = isset($_GET['search']) ? trim($_GET['search']) : '';
$sort_filter = isset($_GET['sort']) ? $_GET['sort'] : 'newest';

$sql = "SELECT p.*, c.name as category_name 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.category_id 
        WHERE 1=1";
$params = [];

if ($category_filter > 0) {
    $sql .= " AND p.category_id = ?";
    $params[] = $category_filter;
}

if (!empty($search_filter)) {
    $sql .= " AND (p.name LIKE ? OR p.description LIKE ?)";
    $params[] = "%$search_filter%";
    $params[] = "%$search_filter%";
}

switch ($sort_filter) {
    case 'price_low':
        $sql .= " ORDER BY p.price ASC";
        break;
    case 'price_high':
        $sql .= " ORDER BY p.price DESC";
        break;
    case 'name_asc':
        $sql .= " ORDER BY p.name ASC";
        break;
    default:
        $sql .= " ORDER BY p.product_id DESC";
}

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $products = $stmt->fetchAll();
} catch (PDOException $e) {
    $products = [];
}

try {
    $stmt = $pdo->query("SELECT * FROM categories ORDER BY name");
    $categories = $stmt->fetchAll();
} catch (PDOException $e) {
    $categories = [];
}
?>

<main class="container">
    <header>
        <h1 style="color: var(--primary); margin: 30px 0 20px;">كل المعروض عليه عروض</h1>
    </header>

    <section class="filter-section" aria-label="تصفية المنتجات">
        <form action="" method="GET" style="display: flex; flex-wrap: wrap; gap: 15px; align-items: center;">
            <select name="category" id="categoryFilter" onchange="this.form.submit()">
                <option value="0">كل الاصناف الي عنا:</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= (int) $cat['category_id'] ?>" <?= $category_filter == $cat['category_id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cat['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <select name="sort" id="sortFilter" onchange="this.form.submit()">
                <option value="newest" <?= $sort_filter == 'newest' ? 'selected' : '' ?>>احدث الموديلات</option>
                <option value="price_low" <?= $sort_filter == 'price_low' ? 'selected' : '' ?>>💰 السعر: منخفض ← مرتفع
                </option>
                <option value="price_high" <?= $sort_filter == 'price_high' ? 'selected' : '' ?>>💰 السعر: مرتفع ← منخفض
                </option>
                <option value="name_asc" <?= $sort_filter == 'name_asc' ? 'selected' : '' ?>>🔤 حسب الاسم</option>
            </select>

            <input type="text" name="search" placeholder="بتدور ع اشي معين؟"
                value="<?= htmlspecialchars($search_filter) ?>"
                style="flex: 1; min-width: 200px; padding: 10px 16px; border: 2px solid #e0e0e0; border-radius: 8px;">

            <button type="submit" class="btn btn-primary">بحث</button>

            <?php if ($category_filter > 0 || !empty($search_filter)): ?>
                <a href="products.php" class="btn btn-outline">حاجة ثانية؟</a>
            <?php endif; ?>
        </form>
    </section>

    <?php if (empty($products)): ?>
        <section class="alert alert-info" style="text-align: center; padding: 40px;">
            <h3>بنوفرها بالايام الجاية ان شاء الله</h3>
            <p>ارجع لصفحة المنتجات او زبط قائمة البحث</p>
            <a href="index.php" class="btn btn-primary" style="margin-top: 15px;">الصفحة الرئيسية</a>
        </section>
    <?php else: ?>
        <p style="color: var(--gray); margin-bottom: 15px;">التشكيلة الي بتدور عليها عندنا: <?= count($products) ?> منها.
        </p>

        <section class="products-grid">
            <?php foreach ($products as $product): ?>
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
                        <p class="product-category">@ <?= htmlspecialchars($product['category_name'] ?? 'غير مصنف') ?></p>
                        <p class="product-price">₪<?= number_format($product['price'], 2) ?></p>
                        <p
                            style="font-size: 13px; color: <?= $product['stock_quantity'] > 0 ? 'var(--secondary)' : '#d32f2f' ?>;">
                            <?= $product['stock_quantity'] > 0 ? '✅ متوفر (' . (int) $product['stock_quantity'] . ' قطعة)' : '❌ غير متوفر' ?>
                        </p>

                        <nav class="product-actions" aria-label="خيارات المنتج">
                            <a href="product_detail.php?id=<?= (int) $product['product_id'] ?>" class="btn btn-outline"
                                style="flex: 1;">التفاصيل الحلوة:</a>
                            <?php if ($product['stock_quantity'] > 0): ?>
                                <button class="btn btn-secondary add-to-cart" data-product-id="<?= (int) $product['product_id'] ?>"
                                    style="flex: 1;">
                                    عبي الكيس يمعلم
                                </button>
                            <?php else: ?>
                                <button class="btn btn-danger" disabled style="flex: 1;">كان عندنا والله جبر, استنى فتحة
                                    المعبر...</button>
                            <?php endif; ?>
                        </nav>
                    </div>
                </article>
            <?php endforeach; ?>
        </section>
    <?php endif; ?>
</main>

<?php require_once 'includes/footer.php'; ?>