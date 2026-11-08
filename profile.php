<?php
$page_title = 'الملف الشخصي - Arya Shope';
require_once 'includes/config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$user_id = (int) $_SESSION['user_id'];
$error = '';
$success = '';

try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();

    if (!$user) {
        header('Location: logout.php');
        exit;
    }
} catch (PDOException $e) {
    header('Location: logout.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error = '❌ خطأ أمني (CSRF)، يرجى تحديث الصفحة والمحاولة مرة أخرى';
    } else {
        $full_name = trim($_POST['full_name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $address = trim($_POST['address'] ?? '');

        if (empty($full_name)) {
            $error = '⚠️ الاسم الكامل مطلوب';
        } else {
            try {
                $stmt = $pdo->prepare("
                    UPDATE users 
                    SET full_name = ?, phone = ?, address = ? 
                    WHERE user_id = ?
                ");
                $stmt->execute([$full_name, $phone, $address, $user_id]);

                $_SESSION['full_name'] = $full_name;

                $success = '✅ تم تحديث الملف الشخصي بنجاح';

                $user['full_name'] = $full_name;
                $user['phone'] = $phone;
                $user['address'] = $address;

            } catch (PDOException $e) {
                $error = '❌ حدث خطأ في النظام، حاول مرة أخرى لاحقاً';
            }
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error = '❌ خطأ أمني (CSRF)، يرجى تحديث الصفحة والمحاولة مرة أخرى';
    } else {
        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
            $error = '⚠️ الرجاء ملء جميع الحقول';
        } elseif ($new_password !== $confirm_password) {
            $error = '⚠️ كلمة المرور الجديدة غير متطابقة مع تأكيدها';
        } elseif (strlen($new_password) < 6) {
            $error = '⚠️ كلمة المرور يجب أن تكون 6 أحرف على الأقل';
        } elseif (!password_verify($current_password, $user['password'])) {
            $error = '❌ كلمة المرور الحالية غير صحيحة';
        } else {
            try {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE user_id = ?");
                $stmt->execute([$hashed_password, $user_id]);
                $success = '✅ تم تغيير كلمة المرور بنجاح';
            } catch (PDOException $e) {
                $error = '❌ حدث خطأ في النظام، حاول مرة أخرى';
            }
        }
    }
}

require_once 'includes/header.php';
?>

<main class="container" style="padding: 30px 0;">
    <header>
        <h1 style="color: var(--primary); margin-bottom: 10px;">👤 الملف الشخصي</h1>
        <p style="color: var(--gray); margin-bottom: 30px;">نورتنا، <?= htmlspecialchars($user['full_name']) ?>!</p>
    </header>

    <?php if ($error): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">
        <section
            style="background: white; padding: 25px; border-radius: var(--radius); border: 1px solid var(--border-color); box-shadow: var(--shadow);">
            <header>
                <h2 style="margin-bottom: 20px; color: var(--primary); font-size: 1.17em;">تعديل بياناتك</h2>
            </header>

            <form method="POST" action="">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

                <fieldset class="form-group" style="border: none; padding: 0; margin: 0 0 18px 0;">
                    <label for="full_name">👤 الاسم الكامل</label>
                    <input type="text" id="full_name" name="full_name" required
                        value="<?= htmlspecialchars($user['full_name']) ?>" placeholder="أدخل اسمك الكامل">
                </fieldset>

                <fieldset class="form-group" style="border: none; padding: 0; margin: 0 0 18px 0;">
                    <label for="email">📧 البريد الإلكتروني</label>
                    <input type="email" id="email" value="<?= htmlspecialchars($user['email']) ?>" disabled
                        style="background: #f5f5f5; cursor: not-allowed;">
                    <small style="color: var(--gray); font-size: 12px;">لا يمكن تغيير البريد الإلكتروني</small>
                </fieldset>

                <fieldset class="form-group" style="border: none; padding: 0; margin: 0 0 18px 0;">
                    <label for="phone">📱 رقم الهاتف</label>
                    <input type="tel" id="phone" name="phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>"
                        placeholder="0599123456">
                </fieldset>

                <fieldset class="form-group" style="border: none; padding: 0; margin: 0 0 18px 0;">
                    <label for="address">📍 العنوان</label>
                    <textarea id="address" name="address" rows="3"
                        placeholder="أدخل عنوانك الكامل"><?= htmlspecialchars($user['address'] ?? '') ?></textarea>
                </fieldset>

                <button type="submit" name="update_profile" class="btn btn-primary" style="width: 100%;">
                    حفظ التغييرات
                </button>
            </form>
        </section>

        <section
            style="background: white; padding: 25px; border-radius: var(--radius); border: 1px solid var(--border-color); box-shadow: var(--shadow);">
            <header>
                <h2 style="margin-bottom: 20px; color: var(--secondary); font-size: 1.17em;">تغيير كلمة المرور</h2>
            </header>

            <form method="POST" action="">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

                <fieldset class="form-group" style="border: none; padding: 0; margin: 0 0 18px 0;">
                    <label for="current_password">🔒 كلمة المرور الحالية</label>
                    <input type="password" id="current_password" name="current_password" required
                        placeholder="••••••••">
                </fieldset>

                <fieldset class="form-group" style="border: none; padding: 0; margin: 0 0 18px 0;">
                    <label for="new_password">🔒 كلمة المرور الجديدة</label>
                    <input type="password" id="new_password" name="new_password" required
                        placeholder="•••••••• (6 أحرف على الأقل)">
                </fieldset>

                <fieldset class="form-group" style="border: none; padding: 0; margin: 0 0 18px 0;">
                    <label for="confirm_password">🔒 تأكيد كلمة المرور الجديدة</label>
                    <input type="password" id="confirm_password" name="confirm_password" required
                        placeholder="••••••••">
                </fieldset>

                <button type="submit" name="change_password" class="btn btn-secondary" style="width: 100%;">
                    تغيير كلمة المرور
                </button>
            </form>

            <aside
                style="margin-top: 20px; padding: 15px; background: var(--primary-bg); border-radius: 8px; border-right: 3px solid var(--primary);">
                <p style="font-size: 13px; color: var(--gray); margin: 0;">
                    <strong>نصائح لكلمة مرور قوية:</strong>
                </p>
                <ul
                    style="font-size: 13px; color: var(--gray); margin: 5px 0; padding-right: 20px; list-style-position: inside;">
                    <li>استخدم 8 أحرف على الأقل</li>
                    <li>اجمع بين أحرف كبيرة وصغيرة</li>
                    <li>أضف أرقام ورموز خاصة</li>
                </ul>
            </aside>
        </section>
    </div>

    <nav style="margin-top: 30px; display: flex; gap: 15px; flex-wrap: wrap; justify-content: center; padding: 20px; background: white; border-radius: var(--radius); border: 1px solid var(--border-color);"
        aria-label="روابط الملف الشخصي">
        <a href="order_history.php" class="btn btn-outline" style="padding: 10px 25px;">📋 تاريخ الطلبات</a>
        <a href="cart.php" class="btn btn-outline" style="padding: 10px 25px;">🛒 سلة التسوق</a>
        <a href="products.php" class="btn btn-outline" style="padding: 10px 25px;">🛍️ متابعة التسوق</a>
        <a href="logout.php" class="btn btn-danger" style="padding: 10px 25px;">🚪 تسجيل الخروج</a>
    </nav>

    <footer
        style="margin-top: 20px; padding: 15px 20px; background: var(--primary-bg); border-radius: var(--radius); display: flex; justify-content: space-between; flex-wrap: wrap; gap: 10px;">
        <div>
            <span style="color: var(--gray);">📅 تاريخ التسجيل:</span>
            <span style="font-weight: 600;"><?= date('Y-m-d', strtotime($user['created_at'] ?? 'now')) ?></span>
        </div>
        <div>
            <span style="color: var(--gray);">👤 نوع الحساب:</span>
            <span
                style="font-weight: 600; color: <?= $user['role'] === 'admin' ? 'var(--primary)' : 'var(--secondary)' ?>;">
                <?= $user['role'] === 'admin' ? '👤👤 مدير' : 'عميل' ?>
            </span>
        </div>
    </footer>
</main>

<?php require_once 'includes/footer.php'; ?>