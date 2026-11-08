<?php
if (!isset($current_page)) {
    $current_page = basename($_SERVER['PHP_SELF']);
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
    <title><?= htmlspecialchars($page_title ?? 'Arya Shope') ?></title>
    <link rel="stylesheet" href="<?= SITE_URL ?>assets/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;900&display=swap" rel="stylesheet">
</head>

<body>
    <header>
        <div class="container">
            <nav class="navbar">
                <div class="nav-brand">
                    <a href="<?= SITE_URL ?>index.php">
                        <span class="brand-icon">🛍️</span>
                        <span class="brand-name">Arya Shope</span>
                    </a>
                </div>

                <ul class="nav-links" id="navLinks">
                    <li>
                        <a href="<?= SITE_URL ?>index.php"
                            class="nav-link <?= $current_page == 'index.php' ? 'active' : '' ?>">
                            <span class="nav-icon">🏠</span> الرئيسية
                        </a>
                    </li>
                    <li>
                        <a href="<?= SITE_URL ?>products.php"
                            class="nav-link <?= $current_page == 'products.php' || $current_page == 'product_detail.php' ? 'active' : '' ?>">
                            <span class="nav-icon">👕</span> المنتجات
                        </a>
                    </li>
                    <li>
                        <a href="<?= SITE_URL ?>cart.php"
                            class="nav-link cart-link <?= $current_page == 'cart.php' ? 'active' : '' ?>">
                            <span class="nav-icon">🛒</span> السلة
                            <?php if (isset($_SESSION['user_id'])): ?>
                                <span class="cart-badge" id="cartCount">
                                    <?php
                                    if (isset($pdo)) {
                                        try {
                                            $stmt = $pdo->prepare("SELECT SUM(quantity) as total FROM cart_items WHERE user_id = ?");
                                            $stmt->execute([$_SESSION['user_id']]);
                                            $result = $stmt->fetch();
                                            echo htmlspecialchars($result['total'] ?? 0);
                                        } catch (PDOException $e) {
                                            echo '0';
                                        }
                                    }
                                    ?>
                                </span>
                            <?php endif; ?>
                        </a>
                    </li>
                    <li>
                        <a href="<?= SITE_URL ?>contact.php"
                            class="nav-link <?= $current_page == 'contact.php' ? 'active' : '' ?>">
                            <span class="nav-icon">📞</span> اتصل بنا
                        </a>
                    </li>

                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li>
                            <a href="<?= SITE_URL ?>profile.php"
                                class="nav-link profile-link <?= $current_page == 'profile.php' ? 'active' : '' ?>">
                                <span class="nav-icon">👤</span>
                                <?= htmlspecialchars($_SESSION['full_name']) ?>
                            </a>
                        </li>
                        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                            <li>
                                <a href="<?= SITE_URL ?>admin/dashboard.php"
                                    class="nav-link admin-link <?= strpos($current_page, 'dashboard') !== false ? 'active' : '' ?>">
                                    <span class="nav-icon">📊</span> لوحة التحكم
                                </a>
                            </li>
                        <?php endif; ?>
                        <li>
                            <a href="<?= SITE_URL ?>logout.php" class="nav-link logout-link">
                                <span class="nav-icon">🚪</span> تسجيل خروج
                            </a>
                        </li>
                    <?php else: ?>
                        <li>
                            <a href="<?= SITE_URL ?>login.php"
                                class="nav-link login-link <?= $current_page == 'login.php' ? 'active' : '' ?>">
                                <span class="nav-icon">🔑</span> تسجيل الدخول
                            </a>
                        </li>
                        <li>
                            <a href="<?= SITE_URL ?>register.php"
                                class="nav-link register-link <?= $current_page == 'register.php' ? 'active' : '' ?>">
                                <span class="nav-icon">📝</span> إنشاء حساب
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>

                <button class="menu-toggle" id="menuToggle" aria-label="فتح القائمة">
                    <span></span>
                    <span></span>
                    <span></span>
                </button>
            </nav>
        </div>
    </header>
    <main>