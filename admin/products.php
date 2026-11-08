<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include '../includes/config.php';
include '../includes/header.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$message = '';
$error = '';

if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $product_id = (int) $_GET['delete'];
    try {
        $stmt = $pdo->prepare("SELECT image_url FROM products WHERE product_id = ?");
        $stmt->execute([$product_id]);
        $product = $stmt->fetch();

        if ($product && $product['image_url'] && file_exists('../' . $product['image_url'])) {
            unlink('../' . $product['image_url']);
        }

        $stmt = $pdo->prepare("DELETE FROM products WHERE product_id = ?");
        $stmt->execute([$product_id]);
        $message = '✅ تم حذف المنتج بنجاح';
    } catch (PDOException $e) {
        $error = '❌ لا يمكن حذف المنتج لأنه مرتبط بطلبات سابقة';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('❌ خطأ في التحقق الأمني (CSRF)');
    }

    $product_id = isset($_POST['product_id']) ? (int) $_POST['product_id'] : 0;
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price = (float) $_POST['price'];
    $stock_quantity = (int) $_POST['stock_quantity'];
    $category_id = isset($_POST['category_id']) && !empty($_POST['category_id']) ? (int) $_POST['category_id'] : null;

    $image_path = '';
    $old_image = '';

    if ($product_id > 0) {
        $stmt = $pdo->prepare("SELECT image_url FROM products WHERE product_id = ?");
        $stmt->execute([$product_id]);
        $old_product = $stmt->fetch();
        $old_image = $old_product['image_url'] ?? '';
    }

    if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] !== UPLOAD_ERR_NO_FILE) {
        if ($_FILES['product_image']['error'] !== UPLOAD_ERR_OK) {
            if ($_FILES['product_image']['error'] === UPLOAD_ERR_INI_SIZE) {
                $error = '❌ حجم الصورة أكبر من المسموح به في السيرفر.';
            } else {
                $error = '❌ حدث خطأ أثناء رفع الصورة. الكود: ' . $_FILES['product_image']['error'];
            }
        } elseif ($_FILES['product_image']['size'] > 2 * 1024 * 1024) {
            $error = '❌ حجم الصورة كبير جداً. أقصى حجم مسموح هو 2 ميجابايت.';
        } else {
            $upload_dir = '../assets/images/products/';

            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }

            $image_extension = strtolower(pathinfo($_FILES['product_image']['name'], PATHINFO_EXTENSION));
            $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

            if (in_array($image_extension, $allowed_extensions)) {
                $new_filename = uniqid('prod_') . '.' . $image_extension;
                $target_path = $upload_dir . $new_filename;

                if (move_uploaded_file($_FILES['product_image']['tmp_name'], $target_path)) {
                    if ($old_image && file_exists('../' . $old_image)) {
                        unlink('../' . $old_image);
                    }
                    $image_path = 'assets/images/products/' . $new_filename;
                } else {
                    $error = '❌ فشل في نقل الصورة. تأكدي من صلاحيات المجلد.';
                }
            } else {
                $error = '❌ نوع الملف غير مدعوم. المسموح: jpg, jpeg, png, gif, webp';
            }
        }
    } else {
        if ($product_id > 0 && $old_image) {
            $image_path = $old_image;
        }
    }

    if (empty($name) || $price <= 0) {
        $error = '⚠️ الاسم والسعر مطلوبان';
    } elseif (!$error) {
        try {
            if ($product_id > 0) {
                $stmt = $pdo->prepare("
                    UPDATE products 
                    SET name = ?, description = ?, price = ?, stock_quantity = ?, category_id = ?, image_url = ?
                    WHERE product_id = ?
                ");
                $stmt->execute([$name, $description, $price, $stock_quantity, $category_id, $image_path, $product_id]);
                $message = '✅ تم تحديث المنتج بنجاح';
            } else {
                $stmt = $pdo->prepare("
                    INSERT INTO products (name, description, price, stock_quantity, category_id, image_url)
                    VALUES (?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([$name, $description, $price, $stock_quantity, $category_id, $image_path]);
                $message = '✅ تم إضافة المنتج بنجاح';
            }
        } catch (PDOException $e) {
            $error = '❌ حدث خطأ: ' . $e->getMessage();
        }
    }
}

$categories = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();

$products = $pdo->query("
    SELECT p.*, c.name as category_name
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.category_id 
    ORDER BY p.product_id ASC
")->fetchAll();

$edit_product = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE product_id = ?");
    $stmt->execute([(int) $_GET['edit']]);
    $edit_product = $stmt->fetch();
}
?>

<main>
    <h1 style="color: var(--primary); margin-bottom: 20px;">🛍️ إدارة المنتجات</h1>

    <?php if ($message): ?>
        <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <section class="admin-form" style="background: #f8f9fa; padding: 25px; border-radius: 12px; margin-bottom: 30px;">
        <h3 style="margin-bottom: 15px;"><?= $edit_product ? '✏️ تعديل منتج' : '➕ إضافة منتج جديد' ?></h3>
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
            <input type="hidden" name="product_id" value="<?= $edit_product['product_id'] ?? 0 ?>">

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                <fieldset style="border: none; padding: 0; margin: 0;">
                    <label for="name" style="display: block; font-weight: bold; margin-bottom: 5px;">اسم المنتج
                        *</label>
                    <input type="text" id="name" name="name"
                        value="<?= htmlspecialchars($edit_product['name'] ?? '') ?>" required
                        style="width: 100%; padding: 8px 12px; border: 1px solid #ddd; border-radius: 6px;">
                </fieldset>
                <fieldset style="border: none; padding: 0; margin: 0;">
                    <label for="price" style="display: block; font-weight: bold; margin-bottom: 5px;">السعر (شيكل)
                        *</label>
                    <input type="number" id="price" name="price" step="0.01" value="<?= $edit_product['price'] ?? '' ?>"
                        required style="width: 100%; padding: 8px 12px; border: 1px solid #ddd; border-radius: 6px;">
                </fieldset>
                <fieldset style="border: none; padding: 0; margin: 0;">
                    <label for="stock_quantity"
                        style="display: block; font-weight: bold; margin-bottom: 5px;">الكمية</label>
                    <input type="number" id="stock_quantity" name="stock_quantity"
                        value="<?= $edit_product['stock_quantity'] ?? 0 ?>"
                        style="width: 100%; padding: 8px 12px; border: 1px solid #ddd; border-radius: 6px;">
                </fieldset>
                <fieldset style="border: none; padding: 0; margin: 0;">
                    <label for="category_id"
                        style="display: block; font-weight: bold; margin-bottom: 5px;">التصنيف</label>
                    <select id="category_id" name="category_id"
                        style="width: 100%; padding: 8px 12px; border: 1px solid #ddd; border-radius: 6px;">
                        <option value="">بدون تصنيف</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['category_id'] ?>" <?= isset($edit_product) && $edit_product['category_id'] == $cat['category_id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </fieldset>
            </div>

            <fieldset style="border: none; padding: 0; margin: 10px 0 0 0;">
                <label for="description" style="display: block; font-weight: bold; margin-bottom: 5px;">الوصف</label>
                <textarea id="description" name="description" rows="3"
                    style="width: 100%; padding: 8px 12px; border: 1px solid #ddd; border-radius: 6px;"><?= htmlspecialchars($edit_product['description'] ?? '') ?></textarea>
            </fieldset>

            <fieldset style="border: none; padding: 0; margin: 10px 0 0 0;">
                <label for="product_image" style="display: block; font-weight: bold; margin-bottom: 5px;">صورة
                    المنتج</label>
                <?php if ($edit_product && $edit_product['image_url']): ?>
                    <figure style="margin-bottom: 10px;">
                        <img src="../<?= htmlspecialchars($edit_product['image_url']) ?>"
                            style="max-width: 100px; max-height: 100px; border-radius: 8px; border: 1px solid #ddd; padding: 5px;">
                        <figcaption style="font-size: 12px; color: #888;">الصورة الحالية</figcaption>
                    </figure>
                <?php endif; ?>
                <input type="file" id="product_image" name="product_image" accept="image/*"
                    style="padding: 8px; border: 1px dashed #ddd; border-radius: 6px; width: 100%;">
                <p style="font-size: 12px; color: #888; margin-top: 5px;">الامتدادات المسموحة: jpg, jpeg, png, gif, webp
                    (بحد أقصى 2MB)</p>
            </fieldset>

            <button type="submit"
                style="margin-top: 15px; padding: 10px 30px; background: #2c3e50; color: white; border: none; border-radius: 6px; font-size: 16px; cursor: pointer;">
                <?= $edit_product ? '💾 تحديث المنتج' : '➕ إضافة المنتج' ?>
            </button>

            <?php if ($edit_product): ?>
                <a href="products.php"
                    style="margin-top: 15px; padding: 10px 25px; background: #95a5a6; color: white; border: none; border-radius: 6px; text-decoration: none; display: inline-block;">❌
                    إلغاء التعديل</a>
            <?php endif; ?>
        </form>
    </section>

    <section>
        <h3 style="margin-bottom: 15px;">📋 قائمة المنتجات</h3>
        <div style="overflow-x: auto; background: white; border-radius: 12px; border: 1px solid #ddd; padding: 15px;">
            <table style="width: 100%; border-collapse: collapse; font-size: 14px;">
                <thead>
                    <tr style="background: #2c3e50; color: white;">
                        <th style="padding: 12px; text-align: center;">#</th>
                        <th style="padding: 12px; text-align: center;">الصورة</th>
                        <th style="padding: 12px; text-align: right;">الاسم</th>
                        <th style="padding: 12px; text-align: center;">السعر</th>
                        <th style="padding: 12px; text-align: center;">الكمية</th>
                        <th style="padding: 12px; text-align: right;">التصنيف</th>
                        <th style="padding: 12px; text-align: center;">إجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($products) > 0): ?>
                        <?php foreach ($products as $product): ?>
                            <tr style="border-bottom: 1px solid #eee;">
                                <td style="padding: 10px; text-align: center; font-weight: bold;"><?= $product['product_id'] ?>
                                </td>
                                <td style="padding: 10px; text-align: center;">
                                    <?php if ($product['image_url']): ?>
                                        <img src="../<?= htmlspecialchars($product['image_url']) ?>"
                                            style="width: 50px; height: 50px; object-fit: cover; border-radius: 8px;"
                                            alt="<?= htmlspecialchars($product['name']) ?>">
                                    <?php else: ?>
                                        <span style="font-size: 30px;">🖼️</span>
                                    <?php endif; ?>
                                </td>
                                <td style="padding: 10px;"><?= htmlspecialchars($product['name']) ?></td>
                                <td style="padding: 10px; text-align: center; font-weight: bold; color: #e67e22;">
                                    ₪<?= number_format($product['price'], 2) ?></td>
                                <td style="padding: 10px; text-align: center;"><?= $product['stock_quantity'] ?></td>
                                <td style="padding: 10px;"><?= htmlspecialchars($product['category_name'] ?? '-') ?></td>
                                <td style="padding: 10px; text-align: center;">
                                    <nav aria-label="خيارات المنتج">
                                        <a href="products.php?edit=<?= $product['product_id'] ?>"
                                            style="background: #f39c12; color: white; padding: 5px 12px; border-radius: 5px; text-decoration: none; font-size: 12px; margin: 0 2px;"
                                            aria-label="تعديل">✏️</a>
                                        <a href="products.php?delete=<?= $product['product_id'] ?>"
                                            onclick="return confirm('هل أنت متأكد من حذف هذا المنتج؟')"
                                            style="background: #e74c3c; color: white; padding: 5px 12px; border-radius: 5px; text-decoration: none; font-size: 12px; margin: 0 2px;"
                                            aria-label="حذف">🗑️</a>
                                    </nav>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 30px;">⚠️ لا توجد منتجات</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</main>

<?php include '../includes/footer.php'; ?>