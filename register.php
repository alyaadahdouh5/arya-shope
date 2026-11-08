<?php
$page_title = 'إنشاء حساب - Arya Shope';
require_once 'includes/config.php';

if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('❌ خطأ أمني (CSRF)');
    }

    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');

    if (empty($full_name) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = '❌ الرجاء ملء جميع الحقول المطلوبة';
    } elseif ($password !== $confirm_password) {
        $error = '❌ كلمة المرور غير متطابقة';
    } elseif (strlen($password) < 6) {
        $error = '❌ كلمة المرور يجب أن تكون 6 أحرف على الأقل';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = '❌ البريد الإلكتروني غير صحيح';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $error = '❌ هذا البريد الإلكتروني مسجل بالفعل';
            } else {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                $stmt = $pdo->prepare("
                    INSERT INTO users (full_name, email, password, phone, address, role) 
                    VALUES (?, ?, ?, ?, ?, 'customer')
                ");
                $stmt->execute([$full_name, $email, $hashed_password, $phone, $address]);

                $success = '✅ تم إنشاء الحساب بنجاح! يمكنك الآن تسجيل الدخول.';
                $_POST = [];
            }
        } catch (PDOException $e) {
            $error = '❌ حدث خطأ في النظام، حاول مرة أخرى';
        }
    }
}

require_once 'includes/header.php';
?>

<main class="container">
    <section class="auth-form">
        <header>
            <h2>📝 إنشاء حساب جديد</h2>
        </header>

        <?php if ($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
            <footer style="text-align: center; margin-top: 15px;">
                <a href="login.php" class="btn btn-primary">🔑 تسجيل الدخول الآن</a>
            </footer>
        <?php else: ?>
            <form method="POST" action="">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

                <fieldset class="form-group" style="border: none; padding: 0; margin: 0 0 18px 0;">
                    <label for="full_name">👤 الاسم الكامل</label>
                    <input type="text" id="full_name" name="full_name" required placeholder="علياء يونس الدحدوح"
                        value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>">
                </fieldset>

                <fieldset class="form-group" style="border: none; padding: 0; margin: 0 0 18px 0;">
                    <label for="email">📧 البريد الإلكتروني</label>
                    <input type="email" id="email" name="email" required placeholder="example@email.com"
                        value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                </fieldset>

                <fieldset class="form-group" style="border: none; padding: 0; margin: 0 0 18px 0;">
                    <label for="password">🔒 كلمة المرور</label>
                    <input type="password" id="password" name="password" required placeholder="•••••••• (6 أحرف على الأقل)">
                </fieldset>

                <fieldset class="form-group" style="border: none; padding: 0; margin: 0 0 18px 0;">
                    <label for="confirm_password">🔒 تأكيد كلمة المرور</label>
                    <input type="password" id="confirm_password" name="confirm_password" required placeholder="••••••••">
                </fieldset>

                <fieldset class="form-group" style="border: none; padding: 0; margin: 0 0 18px 0;">
                    <label for="phone">📱 رقم الهاتف</label>
                    <input type="tel" id="phone" name="phone" required placeholder="0599123456"
                        value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
                </fieldset>

                <fieldset class="form-group" style="border: none; padding: 0; margin: 0 0 18px 0;">
                    <label for="address">📍 العنوان</label>
                    <textarea id="address" name="address" rows="2"
                        placeholder="غزة - فلسطين"><?= htmlspecialchars($_POST['address'] ?? '') ?></textarea>
                </fieldset>

                <button type="submit" class="btn btn-primary" style="width: 100%;">إنشاء حساب</button>

                <footer class="form-footer">
                    لديك حساب بالفعل؟ <a href="login.php">تسجيل الدخول</a>
                </footer>
            </form>
        <?php endif; ?>
    </section>
</main>

<?php require_once 'includes/footer.php'; ?>