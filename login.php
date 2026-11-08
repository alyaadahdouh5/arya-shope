<?php
$page_title = 'تسجيل الدخول - Arya Shope';
require_once 'includes/config.php';
require_once 'includes/header.php';

if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('❌ خطأ أمني (CSRF)');
    }

    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = '⚠️ الرجاء إدخال البريد الإلكتروني وكلمة المرور';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                session_regenerate_id(true);

                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['email'] = $user['email'];

                if ($user['role'] === 'admin') {
                    header('Location: admin/dashboard.php');
                } else {
                    header('Location: index.php');
                }
                exit;
            } else {
                $error = '❌ البريد الإلكتروني أو كلمة المرور غير صحيحة';
            }
        } catch (PDOException $e) {
            $error = '❌ حدث خطأ في النظام، حاول مرة أخرى لاحقاً';
        }
    }
}
?>

<main class="container">
    <section class="auth-form">
        <header>
            <h2>🔐 تسجيل الدخول</h2>
        </header>

        <?php if ($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

            <fieldset class="form-group" style="border: none; padding: 0; margin: 0 0 18px 0;">
                <label for="email">📧 البريد الإلكتروني</label>
                <input type="email" id="email" name="email" required placeholder="example@email.com"
                    value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            </fieldset>

            <fieldset class="form-group" style="border: none; padding: 0; margin: 0 0 18px 0;">
                <label for="password">🔒 كلمة المرور</label>
                <input type="password" id="password" name="password" required placeholder="••••••••">
            </fieldset>

            <button type="submit" class="btn btn-primary" style="width: 100%;">🔑 تسجيل الدخول</button>

            <footer class="form-footer">
                ليس لديك حساب؟ <a href="register.php">📝 إنشاء حساب جديد</a>
            </footer>
        </form>
    </section>
</main>

<?php require_once 'includes/footer.php'; ?>